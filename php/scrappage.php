<?php //Permite acesso crossorigin
header("Access-Control-Allow-Origin: *");

//Recupera dados enviados via javascript no body da requisição
$dados = file_get_contents('php://input');

//var_dump($dados);

//converte para objeto
$json = json_decode($dados);

//Faz a requisição utilizando cURL para obter a estrutura do site
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_URL, $json->url);

//Executa e obtém o código do site
$resposta = curl_exec($ch);
curl_close($ch);

//Declara objeto que será estruturado para resposta da API
$jsonResposta = [];

//Cria estrutura para iniciar o processamento do site
$site = new DOMDocument();
// set error level
$internalErrors = libxml_use_internal_errors(true);
$site->loadHTML($resposta);

//Recupera o título da página
$tituloTags = $site->getElementsByTagName('title');
$tituloSite = "";
foreach($tituloTags as $titulo) {
	$tituloSite = $titulo->nodeValue;
}

//Recupera a descrição da página
$descricaoTags = $site->getElementsByTagName('meta');
$descricao = "";
foreach($descricaoTags as $metaTag) {
	if($metaTag->getAttribute("name") == "description") {
		$descricao = $metaTag->getAttribute("content");
	}
}

//Recupera os elementos que são links
$linksTags = $site->getElementsByTagName('a');
$links = [];
foreach($linksTags as $linkTag) {
	$href = $linkTag->getAttribute("href");
	//Filtra links vazios ou links âncora, e impede de inserir duplicados
	if($href !== "" && strpos($href, "#") !== 0 && !in_array($href, $links)) {
		array_push($links, $href);
	}
}



//Recupera os elementos que são imagens
$imagensTags = $site->getElementsByTagName('img');
$imagens = [];
foreach($imagensTags as $imagemTag) {
	$src = $imagemTag->getAttribute("src");
	if($src !== "" && !in_array($src, $imagens)) {
		//Insere as imagens, convertendo de endereço relativo pra absoluto, para que seja possível utilizar a url das imagens, e impede de inserir duplicados
		array_push($imagens, rel2abs($imagemTag->getAttribute("src"), $json->url));
	}
}


//Estrutura o objeto de resposta
$jsonResposta = array(
	"titulo" => $tituloSite,
	"descricao" => $descricao,
	"links" => array_unique($links),
	"imagens" => array_unique($imagens)
);

//Responde com os dados convertidos em uma string json
echo json_encode($jsonResposta);


//Converte um endereço relativo para o endereço absoluto
//https://stackoverflow.com/questions/4444475/transform-relative-path-into-absolute-url-using-php
function rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}