<?php

namespace App;

use App\Traits\DocumentTrait;

class Utils
{
    use DocumentTrait;

    public function attacheddocumentname($identification, $file)
    {
        try{
            if(file_exists(storage_path("app/public/{$identification}/"."RptaFE-".substr($file, 11, strpos($file, '.')))))
               $rptaxml = file_get_contents(storage_path("app/public/{$identification}/"."RptaFE-".substr($file, 11, strpos($file, '.'))));
            else
                if(file_exists(storage_path("app/public/{$identification}/"."RptaNC-".substr($file, 11, strpos($file, '.')))))
                    $rptaxml = file_get_contents(storage_path("app/public/{$identification}/"."RptaNC-".substr($file, 11, strpos($file, '.'))));
                else
                    $rptaxml = file_get_contents(storage_path("app/public/{$identification}/"."RptaND-".substr($file, 11, strpos($file, '.'))));

            $filename = str_replace('ads', 'ad', str_replace('dse', 'ad', str_replace('na', 'ad', str_replace('ni', 'ad', str_replace('nd', 'ad', str_replace('nc', 'ad', str_replace('fv', 'ad', $this->getTag($rptaxml, "XmlFileName")->nodeValue)))))));
            return $filename;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
