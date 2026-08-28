<?php

namespace app\models;

use ishop\App;

class Category extends AppModel {

    /** @return list<int> */
    public function getIdList(int $id): array
    {
        $categories = App::$app->getProperty('cats');
        $result = [];
        $visited = [];
        $walk = function (int $parentId) use (&$walk, &$result, &$visited, $categories): void {
            if (isset($visited[$parentId])) {
                return;
            }
            $visited[$parentId] = true;
            foreach ($categories as $categoryId => $category) {
                if ((int)$category['parent_id'] === $parentId) {
                    $walk((int)$categoryId);
                }
            }
            $result[] = $parentId;
        };
        $walk($id);

        return array_values(array_unique(array_filter($result)));
    }

    public function getIds($id){
        $ids = array_values(array_filter(
            $this->getIdList((int)$id),
            static fn(int $categoryId): bool => $categoryId !== (int)$id
        ));
        return $ids === [] ? null : implode(',', $ids) . ',';
    }


}
