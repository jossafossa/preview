<?php
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
  $data['title'] = $titleMatch ? $titleMatch[1] : '';

  // Extract meta description
  $metaDescRegex = '/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i';
  preg_match($metaDescRegex, $html, $metaDescMatch);
  $data['metaDescription'] = $metaDescMatch ? $metaDescMatch[1] : '';

  // Extract thumbnail
  $thumbnailRegex = '/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']/i';
  preg_match($thumbnailRegex, $html, $thumbnailMatch);
  $data['thumbnail'] = $thumbnailMatch ? $thumbnailMatch[1] : '';

  return $data;
}

// Check if the request contains a URL parameter
if (isset($_POST['url'])) {
  $url = $_POST['url'];
  $previewData = getPreviewData($url);
  echo json_encode($previewData);
}
?>
