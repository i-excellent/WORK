<?php
set_time_limit(999999);
class Proxy{
    private $dbh;
    public  function __construct()
{
    $this->dbh = new PDO('mysql:host=185.65.246.44;dbname=greatmake','excellent','411826403');
}
    /**
     * ������ ����� �������� ��� ��������� ip � ����
     * � ������� 11.11.11.11:22
     * @param string $url ����� ��� ���������� ������� �������
     * @return array full page and errors
     */
public function get_web_page( $url )

{
    $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // ���������� ���-��������
    curl_setopt($ch, CURLOPT_HEADER, 0);           // �� ���������� ���������
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // ��������� �� ����������
    curl_setopt($ch, CURLOPT_ENCODING, "");        // ������������ ��� ���������
    curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); // ������� ����������
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);        // ������� ������
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // ��������������� ����� 10-��� ���������

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
     * ���� ip � ����� � ������
     * � ������� 11.11.11.11:22
     */

    public function getIpPort( $url ){
$result = $this->get_web_page( $url );
$err = 0;
if ( $result['errno'] != 0 )
{ //... ������: ������������ url, �������, ������������ ... ���������� �� �������
    throw new \Exception('������');
}

if ( $result['http_code'] != 200 )
{ //... ������: ��� ��������, ��� ���� ... ���������� �� �������
    throw new \Exception('��� ������ �� �����');
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
     * ��������� ip �� ����������������,
     * ���� ���������� �� ���������� � �L
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
     * ������ � ��
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
     * ����� ���������� �����
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
$start->getIpPort('http://fineproxy.org/freshproxy/#more-6');//������ ������ ������
