<?php
// 1. Izinkan aplikasi IPTV mengakses sistem ini
header("Access-Control-Allow-Origin: https://www.vidio.com");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST");
header("Content-Type: application/dash+xml");

// Membaca ID otomatis dari file M3U (Contoh: ?id=782)
$id = isset($_GET['id']) ? $_GET['id'] : '204'; 

// 2. Ambil token dinamis (hdntl) terbaru secara otomatis dari API Vidio
$token_url = "https://www.vidio.com/api/tokens";
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
    'Referer: https://www.vidio.com/'
]);
$token_response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($token_response, true);
$token = isset($token_data['token']) ? $token_data['token'] : '';

if (empty($token)) {
    http_response_code(500);
    die("Gagal memperbarui token keamanan.");
}

// 3. STRUKTUR YANG BENAR: Menyisipkan ID saluran secara otomatis ke dalam alamat CDN Akamai Vidio
$final_stream_url = "https://akamaized.net{$id}/file/stream.mpd?" . $token;

// 4. REKAYASA HEADER PENUH: Meniru 100% data header browser yang Anda kirimkan
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36\r\n" .
                    "Accept: */*\r\n" .
                    "Accept-Language: en-US,en;q=0.9,id;q=0.8\r\n" .
                    "Origin: https://www.vidio.com\r\n" .
                    "Referer: https://www.vidio.com/\r\n" .
                    "sec-ch-ua: \"Chromium\";v=\"148\", \"Google Chrome\";v=\"148\", \"Not/A)Brand\";v=\"99\"\r\n" .
                    "sec-ch-ua-mobile: ?0\r\n" .
                    "sec-ch-ua-platform: \"Windows\"\r\n" .
                    "sec-fetch-dest: empty\r\n" .
                    "sec-fetch-mode: cors\r\n" .
                    "sec-fetch-site: cross-site\r\n"
    ]
];

// 5. Unduh video mentah dengan penyamaran sempurna dan alirkan ke IPTV Anda
$context = stream_context_create($opts);
$video_content = file_get_contents($final_stream_url, false, $context);

echo $video_content;
?>
