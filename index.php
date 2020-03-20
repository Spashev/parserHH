<?php
 
echo '<b>Parsing hh.kz</b><hr>';
/**
 * Запуск парсинга по страницам
 */
function startParsing($page) 
{
    // получаем код страницы
    $subject = file_get_contents('https://hh.kz/search/vacancy?L_is_autosearch=false&area=160&clusters=true&enable_snippets=true&text=php&page=' . $page);
    //Шаблон для название и ссылки
    $pattern = '/<a class="bloko-link HH-LinkModifier" data-qa="vacancy-serp__vacancy-title" href="(.+?)" data-position="[0-9]+" data-requestId=".+?" data-totalVacancies="[0-9]+" target="_blank">(.+?)<\/a>/u';
    $match = []; //корзина куда собираем нахождения по шаблону
    preg_match_all($pattern, $subject, $match); //ищем по шаблону
    /**
     * формируем массив для удобной чтения данных
     */
    $results = [];
    foreach($match[2] as $key=>$value) {
        $results[] = [
            'name' => $value,
            'link' => $match[1][$key]
        ];
    }
    /**
     * получаем данные вакансий и записываем в соответствии
     */
    foreach($results as $key => $result) {
        $page = file_get_contents($result['link']);
        // вызов функций он вернет массив и мы проходимся по элементам и записываем их в наш чиатбельный массив
        foreach(getDetails($page) as $k=>$page) {
            $results[$key][$k] = $page;
        }
    }

    return $results;
}

/**
 * Функция который вазвращает массив и описаниями
 */
function getDetails($subject)
{
    $patternCompany = '/class="bloko-section-header-2 bloko-section-header-2_lite">(.+?)<\/span>/u'; //шаблон для названии компании
    $matchCompany = [];
    preg_match_all($patternCompany, $subject, $matchCompany);

    $patternDescription = '/(<div class="g-user-content" data-qa="vacancy-description">(.+?)<\/div>)|(<div class="vacancy-branded-user-content" itemprop="description" data-qa="vacancy-description">(.+?)<\/div>)/u'; //шаблон для описания
    $matchDescription = [];
    preg_match_all($patternDescription, $subject, $matchDescription);
    
    $patternAddress = '/(<span data-qa="vacancy-view-raw-address"><span class="metro-station"><span class="bloko-metro-pin" style="background-color:#CD0505"><\/span>(.+?)<\/span>, <!-- -->(.+?)<!-- -->(.+?)<\/span>)|(<span data-qa="vacancy-view-raw-address">(.+?)<\/span>)|(<p data-qa="vacancy-view-location">(.+?)<\/p>)/u'; //шаблон для адреса
    $matchAddress = [];
    preg_match_all($patternAddress, $subject, $matchAddress);

    $patternPrice = '/class="bloko-header-2 bloko-header-2_lite">(.+?)<\/span>/u'; //шаблон для ЗП
    $matchPrice = [];
    preg_match_all($patternPrice, $subject, $matchPrice);
    /**
     * формируем массив
     */
    $results = [
    'salary' =>  $matchPrice[1][0],
    'address' => $matchAddress[0][0],
    'company_name' => $matchCompany[1][0],
    'options' => $matchDescription[0][0] ? $matchDescription[0][0] : $matchDescription[4][0]
    ];

    return $results;
}

/**
 * Функия вазвращает мксимальное количество страниц
 */
function getMaxPage()
{
$subject = file_get_contents('https://hh.kz/search/vacancy?L_is_autosearch=false&area=160&clusters=true&enable_snippets=true&text=php&page=0'); // код страницы
$pattern = '/<a class="bloko-button HH-Pager-Control" data-page="[0-9]+" data-qa="pager-page" rel="nofollow" href="\/search\/vacancy\?L_is_autosearch=false&amp;area=160&amp;clusters=true&amp;enable_snippets=true&amp;text=php&amp;page=[0-9]+">([0-9]+)<\/a>/u'; // шаблон для пагинации
$match = [];
preg_match_all($pattern, $subject,$match);
$max = 0;
/**
 * проверка на максимальное число, то есть это масимальное количество страниц
 */
foreach($match[1] as $item) {
    if($item > $max) {
        $max = $item;
    }
}
return $max;
}

$maxPage = getMaxPage(); // узнаем сколько страниц

// Глобальный массив для записи данных со всех страниц
$data = [];
// Запуск парсинга по страницам
for($i = 0; $i < $maxPage; $i++) {
$data = array_merge($data, startParsing($i)); // объединения результата
}
echo '<pre>';
print_r($data);
echo '</pre>';