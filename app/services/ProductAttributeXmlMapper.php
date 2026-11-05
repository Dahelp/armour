<?php
declare(strict_types=1);

namespace app\services;

final class ProductAttributeXmlMapper
{
    /** @return list<array{attribute_id:int,attribute_group_id:int,text:string}> */
    public function map(\SimpleXMLElement $offer, array $definitions): array
    {
        $normalise = static fn(string $text): string => mb_strtolower(trim($text), 'UTF-8');
        $result = [];
        $seen = [];
        foreach ($offer->param as $param) {
            $name = $normalise((string)$param['name']);
            $groupName = trim((string)$param['group']);
            $group = null;
            if ($groupName !== '') {
                $groups = array_filter($definitions, static fn(array $row): bool => (int)$row['attribute_group_id'] === 0 && $normalise($row['attribute_name']) === $normalise($groupName));
                if (count($groups) !== 1) {
                    throw new \RuntimeException('Неизвестная группа характеристики: '.$groupName);
                }
                $group = (int)array_values($groups)[0]['id'];
            }
            $key = ($group ?? 'ungrouped').'|'.$name;
            if (isset($seen[$key])) {
                throw new \RuntimeException('Повторная характеристика «'.(string)$param['name'].'». Для частей фильтра укажите разные группы в XML (group="Внешний" / group="Внутренний").');
            }
            $seen[$key] = true;
            $matches = array_filter($definitions, static fn(array $row): bool => (int)$row['attribute_group_id'] > 0 && $normalise($row['attribute_name']) === $name && ($group === null || (int)$row['attribute_group_id'] === $group));
            if ($group === null && count($matches) > 1) {
                $general = array_filter($matches, static fn(array $row): bool => (int)$row['attribute_group_id'] === 1);
                if (count($general) === 1) {
                    $matches = $general;
                }
            }
            if (count($matches) > 1 || ($group !== null && count($matches) !== 1)) {
                throw new \RuntimeException('Неоднозначная характеристика: '.(string)$param['name']);
            }
            if ($matches !== []) {
                $definition = array_values($matches)[0];
                $result[] = ['attribute_id'=>(int)$definition['id'], 'attribute_group_id'=>(int)$definition['attribute_group_id'], 'text'=>(string)$param];
            }
        }
        return $result;
    }
}
