<?php
declare(strict_types=1);
namespace app\services;
final class DocumentProductContent
{
    public static function render(array $source): string
    {
        $paragraphs=$source['paragraphs'];$items=$source['list_items']??[];$html='';$open=false;
        foreach($items as $index)if(!is_int($index)||!isset($paragraphs[$index]))throw new \InvalidArgumentException('Invalid document list position.');
        foreach($paragraphs as $index=>$text) {
            $text=htmlspecialchars($text,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
            if(in_array($index,$items,true)){if(!$open){$html.='<ul>';$open=true;}$html.='<li>'.$text.'</li>';}
            else{if($open){$html.='</ul>';$open=false;}$html.='<p>'.$text.'</p>';}
        }
        return $html.($open?'</ul>':'');
    }
}