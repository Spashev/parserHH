<?php
 
 echo '<b>Parsing hh.kz</b><hr>';

 function startParsing($page) {
    $subject = file_get_contents('https://hh.kz/search/vacancy?L_is_autosearch=false&area=160&clusters=true&enable_snippets=true&text=php&page=' . $page);
    
    $pattern = '/<a class="bloko-link HH-LinkModifier" data-qa="vacancy-serp__vacancy-title" href="(.+?)" data-position="[0-9]+" data-requestId=".+?" data-totalVacancies="[0-9]+" target="_blank">(.+?)<\/a>/u';
    $match = [];
    preg_match_all($pattern, $subject, $match);
    
    $results = [];
    foreach($match[2] as $key=>$value) {
        $results[] = [
            'name' => $value,
            'link' => $match[1][$key]
        ];
    }

    foreach($results as $key => $result) {
        $page = file_get_contents($result['link']);
        $results[$key]['details'] = getDetails($page);
    }

    return $results;
}
 
 function getDetails($subject)
 {
     $patternCompany = '/class="bloko-section-header-2 bloko-section-header-2_lite">(.+?)<\/span>/u';
     $matchCompany = [];
     preg_match_all($patternCompany, $subject, $matchCompany);
 
     $patternDescription = '/(<div class="g-user-content" data-qa="vacancy-description">(.+?)<\/div>)|(<div class="vacancy-branded-user-content" itemprop="description" data-qa="vacancy-description">(.+?)<\/div>)/u';
     $matchDescription = [];
     preg_match_all($patternDescription, $subject, $matchDescription);
     
     $patternAddress = '/(<span data-qa="vacancy-view-raw-address"><span class="metro-station"><span class="bloko-metro-pin" style="background-color:#CD0505"><\/span>(.+?)<\/span>, <!-- -->(.+?)<!-- -->(.+?)<\/span>)|(<span data-qa="vacancy-view-raw-address">(.+?)<\/span>)|(<p data-qa="vacancy-view-location">(.+?)<\/p>)/u';
     $matchAddress = [];
     preg_match_all($patternAddress, $subject, $matchAddress);
 
     $patternPrice = '/class="bloko-header-2 bloko-header-2_lite">(.+?)<\/span>/u';
     $matchPrice = [];
     preg_match_all($patternPrice, $subject, $matchPrice);
 
     $results = [
        'salary' =>  $matchPrice[1][0],
        'address' => $matchAddress[0][0],
        'company_name' => $matchCompany[1][0],
        'options' => $matchDescription[0][0] ? $matchDescription[0][0] : $matchDescription[4][0]
     ];
 
     return $results;
 }
 
 function getMaxPage()
 {
    $subject = file_get_contents('https://hh.kz/search/vacancy?L_is_autosearch=false&area=160&clusters=true&enable_snippets=true&text=php&page=0');
    $pattern = '/<a class="bloko-button HH-Pager-Control" data-page="[0-9]+" data-qa="pager-page" rel="nofollow" href="\/search\/vacancy\?L_is_autosearch=false&amp;area=160&amp;clusters=true&amp;enable_snippets=true&amp;text=php&amp;page=[0-9]+">([0-9]+)<\/a>/u';
    $match = [];
    preg_match_all($pattern, $subject,$match);
    $max = 0;
    foreach($match[1] as $item) {
        if($item > $max) {
            $max = $item;
        }
    }
    return $max;
 }
 
 $maxPage = getMaxPage();
 $data = [];
 for($i = 0; $i < $maxPage; $i++) {
    $data[] = array_merge($data, startParsing($i));
 }
 echo '<pre>';
 print_r($data);
 echo '</pre>';