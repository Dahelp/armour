<?php
declare(strict_types=1);
namespace app\services;

final class LegacyContentRepository
{
    /** @param list<array<string,mixed>> $rows */
    public function insertDrafts(array $rows):int
    {
        \R::begin();
        try{
            foreach($rows as $row){
                $alias=UrlAliasRepository::normaliseSef((string)$row['alias']);
                if($alias===''||\R::findOne('contents','alias = ?',[$alias])||\R::findOne('url_alias','sef = ?',[$alias])){
                    throw new \RuntimeException("Alias уже существует или некорректен: {$alias}");
                }
                $content=\R::dispense('contents');
                $content->type_id=(int)$row['type_id'];$content->name=(string)$row['name'];$content->anons='';
                $content->content=(string)$row['content'];$content->date_post=date('Y-m-d H:i:s');$content->date_last_modified=date('Y-m-d H:i:s');
                $content->alias=$alias;$content->hide='hide';$content->title=(string)$row['title'];$content->description=(string)$row['description'];
                $content->keywords='';$content->img='';$content->user_id=0;$content->clicks=0;$content->img_hide='hide';
                $id=(int)\R::store($content);
                (new UrlAliasRepository())->save($alias,(int)$row['type_id']===3?'News':'Articles',$id);
            }
            \R::commit();
        }catch(\Throwable $exception){\R::rollback();throw $exception;}
        return count($rows);
    }
}
