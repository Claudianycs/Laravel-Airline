<?php

// Prepare local development credentials without overwriting configured values.
$root = dirname(__DIR__);
$path = $root.'/.env';
$content = file_exists($path) ? file_get_contents($path) : file_get_contents($root.'/.env.example');
foreach (['DB_PASSWORD', 'MYSQL_ROOT_PASSWORD', 'DEMO_ADMIN_PASSWORD', 'DEMO_AGENT_PASSWORD', 'DEMO_CUSTOMER_PASSWORD'] as $key) {
    if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $content, $match)) {
        if (trim($match[1], " \t\"'") !== '') {
            continue;
        }
        $content = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $key.'='.bin2hex(random_bytes(18)), $content);
    } else {
        $content .= "\n".$key.'='.bin2hex(random_bytes(18))."\n";
    }
}
if (! preg_match('/^APP_KEY=(.*)$/m', $content, $keyMatch) || trim($keyMatch[1], " \t\"'") === '') {
    $key = 'APP_KEY=base64:'.base64_encode(random_bytes(32));
    $content = preg_match('/^APP_KEY=/m', $content) ? preg_replace('/^APP_KEY=.*$/m', $key, $content) : $content."\n".$key."\n";
}
if (file_put_contents($path, $content, LOCK_EX) === false) {
    throw new RuntimeException('Não foi possível gravar .env.');
}
chmod($path,0600);
echo "Configuração local preparada. Senhas existentes foram preservadas; consulte o arquivo .env para acesso.\n";
