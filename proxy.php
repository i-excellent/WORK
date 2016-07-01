<?php
set_time_limit(999999);
class Proxy{
    private $dbh;
    public  function __construct()
{
    $this->dbh = new PDO('mysql:host=185.65.246.44;dbname=greatmake','excellent','411826403');
}
    /**
     * парсит целую страницу где наход€т€м ip и поры
     * в формате 11.11.11.11:22
     * @param string $url адрес где находл€тс€ проскси сервера
     * @return array full page and errors
     */
public function get_web_page( $url )

{
    $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // возвращает веб-страницу
    curl_setopt($ch, CURLOPT_HEADER, 0);           // не возвращает заголовки
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // переходит по редиректам
    curl_setopt($ch, CURLOPT_ENCODING, "");        // обрабатывает все кодировки
    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // таймаут соединени€
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);        // таймаут ответа
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // останавливатьс€ после 10-ого редиректа

    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}


    /**
     * ищет ip и порты в строке
     * в формате 11.11.11.11:22
     */

    public function getIpPort( $url ){
$result = $this->get_web_page( $url );
$err = 0;
if ( $result['errno'] != 0 )
{ //... ошибка: неправильный url, таймаут, зацикливание ... обработать по желанию
    throw new \Exception('ќшибка');
}

if ( $result['http_code'] != 200 )
{ //... ошибка: нет страницы, нет прав ... обработать по желанию
    throw new \Exception('Ќет ответа от сайта');
}
$page = $result['content'];
if (preg_match_all('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\:[0-9]{1,5}/', $page, $a_matches))
{
    foreach ($a_matches[0] as $a){
        $info = explode(':', $a);
        $ip   = $info['0'];
        $port = $info['1'];
        $this->getCheckProxy($ip,$port);
        }
}
else {
    return false;
}
   return true;
}
    /**
     * провер€ет ip на работосапосность,
     * если нормальный то записывает в ЅL
     */
    public function getCheckProxy($ip,$port)
    {
        if($con = @fsockopen($ip, $port, $eroare, $eroare_str, 2))
        {
            $this->setIP_Port($ip,$port);
            print $ip.$port."+" . '<br>'; // Show the proxy
            fclose($con); // Close the socket handle
            return true;
        }
        else{

            print $ip.$port."-" . '<br>'; // Show the proxy
            return false;
        }

}
    /**
     * «апись в бд
     *
     */
    public function setIP_Port($ip,$port){

        $aRandSoft=$this->getRandomSoft();
        $stmt = $this->dbh->prepare("INSERT INTO proxi (ip, port, browser, os) VALUES (:ip, :port,:browser,:os)");
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':port', $port);
        $stmt->bindParam(':browser', $aRandSoft[0]);
        $stmt->bindParam(':os', $aRandSoft[1]);
        $stmt->execute();
    }
    /**
     * ¬ывод рандомного софта
     *
     */
    public function getRandomSoft(){
        $aBr=['Opera/9.80','Mozilla/5.0','Opera/12.02','Firefox 47.0','Firefox 23.1'];
        $aOS=['Microsoft Windows 7','Linux Mind',
            'Microsoft Windows XP','Windows NT 5.1',
            'Microsoft Windows 10','Microsoft Windows 8.1'];
        $iBr=array_rand($aBr,1);
        $iOS=array_rand($aOS,1);
        return [$aBr[$iBr],$aOS[$iOS]];
    }

}
$start = new Proxy();
$start->getIpPort('http://fineproxy.org/freshproxy/#more-6');//откуда парсим прокси
