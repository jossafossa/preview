<?php
error_reporting(0);

if (isset($_SERVER['HTTP_ORIGIN'])) {
  //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header("Access-Control-Allow-Origin: *");
  header('Access-Control-Allow-Credentials: true');
  header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

  exit(0);
}
?>

<?php
// formats local urls
// ./image.png -> <domain>/image.png
// /image.png -> <domain>/image.png
// image.png -> <domain>/image.png
// https://www.google.com/image.png -> https://www.google.com/image.png

function formatLocalUrl($baseURL, $url) {
  if (!$url) {
    return null;
  }

  // strip the files from the url
  $scheme = parse_url($baseURL, PHP_URL_SCHEME);
  $host = parse_url($baseURL, PHP_URL_HOST);
  $path = parse_url($baseURL, PHP_URL_PORT);
  $port = parse_url($baseURL, PHP_URL_PORT);
  $baseURL = `$scheme://$host:$port/$path`;

  //http or https
  if (preg_match('/^https?:\/\//i', $url)) {
    return $url;
  } else if (preg_match('/^\//i', $url)) {
    return $baseURL . $url;
  } else {
    return $baseURL . '/' . $url;
  }
}


// Sanitize the string
// &amp; -> &
function sanitize($str) {
  return html_entity_decode($str);
}

function get_attribute($input_html, $match_tag, $match_attribute, $match_value, $the_attribute_to_return) {
  $pattern = sprintf(
    '/<%s(?=[^>]*\b%s\s*=\s*["\']%s["\'])(?=[^>]*\b%s\s*=\s*["\'](.*?)["\'])[^>]*>/i',
    preg_quote($match_tag),
    preg_quote($match_attribute),
    preg_quote($match_value),
    preg_quote($the_attribute_to_return)
  );

  if (preg_match($pattern, $input_html, $matches)) {
    return sanitize($matches[1]);
  } else {
    return null;
  }
}


function getPreviewData($url) {
  $data = array(
    'title' => '',
    'thumbnail' => '',
    'metaDescription' => '',
    'url' => $url,
  );


  // Set the appropriate headers
  $options = array(
    'http' => array(
      'method' => "GET",
      'header' => "Accept-language: en\r\n" .
        "Cookie: foo=bar\r\n" .
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36\r\n"  // Updated User-Agent
    )
  );

  $context = stream_context_create($options);
  $html = file_get_contents($url, false, $context);

  // Extract title
  $titleRegex = '/<title>(.*?)<\/title>/i';
  preg_match($titleRegex, $html, $titleMatch);
  $title = $titleMatch ? $titleMatch[1] : '';
  $data['title'] = sanitize($title);

  // Extract meta description
  $metaDescRegex = '/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i';
  preg_match($metaDescRegex, $html, $metaDescMatch);
  $metaDescription = $metaDescMatch ? $metaDescMatch[1] : '';
  $data['metaDescription'] = sanitize($metaDescription);

  // Extract thumbnail
  // $pattern = '/<meta(?=[^>]*\bproperty\s*=\s*["\']og:image["\'])(?=[^>]*\bcontent\s*=\s*["\'](.*?)["\'])[^>]*>/i';
  // preg_match($pattern, $html, $thumbnailMatch);
  // $data['thumbnail'] = formatLocalUrl($url, $thumbnailMatch ? $thumbnailMatch[1] : '');
  $thumbnails = [
    get_attribute($html, 'meta', 'property', 'og:image', 'content'),
    get_attribute($html, 'meta', 'itemprop', 'image', 'content'),
  ];
  $data['thumbnails'] = $thumbnails;
  $thumbnails = array_values(array_filter($thumbnails));
  $data['thumbnail'] = formatLocalUrl($url, $thumbnails[0]);


  // get icon
  $iconRegex = '/<link\s+rel=["\']icon["\']\s+href=["\'](.*?)["\']/i';
  preg_match($iconRegex, $html, $iconMatch);
  $icon = formatLocalUrl($url, $iconMatch ? $iconMatch[1] : '');
  $icons = [
    get_attribute($html, 'link', 'rel', 'apple-touch-icon', 'href'),
    $icon,
    get_attribute($html, 'link', 'rel', 'shortcut icon', 'href'),
    get_attribute($html, 'link', 'rel', 'shortcut', 'href'),
    get_attribute($html, 'link', 'rel', 'icon', 'href'),
  ];
  $data['debug'] = $icons;
  $icons = array_values(array_filter($icons));
  $data['icon'] = formatLocalUrl($url, $icons[0]);

  return $data;
}

// Check if the request contains a URL parameter
if (isset($_POST['url'])) {
  $url = $_POST['url'];
  $previewData = getPreviewData($url);
  echo json_encode($previewData);
}
?>
