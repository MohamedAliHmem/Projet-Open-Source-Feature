<?php
namespace App\Classe;
use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\Dotenv\Dotenv;
class Mail
{
private $api_key="45f93efa41b5f050aadcae047f037190";
private $api_key_secret="fd06a77d68d48dee17ebe91b92c4f41d";
/* public function __construct()
{
(new Dotenv())->bootEnv(dirname(__DIR__) . '/../.env');
$this->api_key = $_ENV['API_KEY'];
$this->api_key_secret = $_ENV['API_KEY_SECRET'];
} */
public function send($to_email, $to_name, $subject, $content)
{
$mj = new Client($this->api_key, $this->api_key_secret, true, ['version'
=> 'v3.1']);
$body = [
'Messages' => [
[
'From' => [
'Email' => "bellilihamza952@gmail.com",
'Name' => "Reset"
],
'To' => [
[
'Email' => $to_email,
'Name' => $to_name
]
],
'TemplateID' =>  5926718,
'TemplateLanguage' => true,
'Subject' => $subject,
'Variables' => [
'content' => $content
]
]
]
];
$response = $mj->post(Resources::$Email, ['body' => $body]);
$response->success();
}
}