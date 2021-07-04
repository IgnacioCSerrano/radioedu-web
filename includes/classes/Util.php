<?php
require_once Constants::INC_EMAIL_CLASS;
require_once Constants::INC_PHPMAILER;
require_once Constants::INC_PHPMAILER_SMTP;
require_once Constants::INC_PHPMAILER_EXC;

use PHPMailer\PHPMailer\PHPMailer;

class Util
{
    /**
     * Muestra en la consola del intérprete del navegador el mensaje (variable o literal) 
     * introducido como parámetro, de manera análoga a la función console.log() de JavaScript.
     * El segundo parámetro opcional.
     */
    public function console_log($output, $with_script_tags = true)
    {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }

    /**
     * Redirecciona a la url indicada como parámetro.
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Retorna atributo 'activo' para mostrar resaltado el enlace del menú de navegación
     * correspondiente a la ruta actual.
     */
    public function echoActiveIfRequestMatches($requestPath)
    {
        $parts = explode('/', $_SERVER['REQUEST_URI']);
        if ( $parts[ count($parts) - 2] == $requestPath ) {
            echo 'active';
        }
    }

    /**
     * Permite pasar un array de variables con la función include (útil para incluir bloque de cabecera
     * con títulos y/o hojas de estilo personalizadas).
     */
    public function includeWithVariables($filePath, $variables = array(), $print = true)
    {
        $output = NULL;
        if ( file_exists($filePath) ) {
            // Extract the variables to a local namespace
            extract($variables);

            // Start output buffering
            ob_start();

            // Include the template file
            include $filePath;

            // End buffering and return its contents
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }

    /**
     * Genera número aleatorio criptográficamente seguro comprendido entre rango de números 
     * pasados como parámetros.
     */
    public function cryptoRandSecure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }
        $log = ceil( log($range, 2) );
        $bytes = (int) ($log / 8) + 1;      // length in bytes
        $bits = (int) $log + 1;             // length in bits
        $filter = (int) (1 << $bits) - 1;   // set all lower bits to 1 (bits de bajo orden)
        do {
            $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes($bytes) ) );
            $rnd = $rnd & $filter;  // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    /**
     * Genera una cadena aleatoria (criptográficamente segura) compuesta por cadena de 
     * caracteres y longitud indicados como parámetros.
     */
    public function generateCode($codeAlphabet, $length)
    {
        $token = '';
        $max = strlen($codeAlphabet);
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->cryptoRandSecure(0, $max)];
        }
        return $token;
    }

    /**
     * Limpia las cookies de autenticación del navegador web.
     */
    public function clearAuthCookie()
    {
        if ( isset( $_COOKIE['username'] ) ) {
            setcookie('username', '');
        }
        if ( isset( $_COOKIE['token'] ) ) {
            setcookie('token', '');
        }
    }

    /**
     * Sube imagen a servidor y retorna ruta generada.
     */
    public function uploadImage($path, $file, $defaultPath)
    {
        if ( !empty( basename($file['name']) ) ) {
            $filename = $this->generateCode(Constants::CODE_ALPHANUMERIC, 16) . '.' . 
                pathinfo($file['name'], PATHINFO_EXTENSION);
            if ( getimagesize( $file['tmp_name'] ) ) {
                $targetFilePath = ROOT_PATH . $path . $filename;
                if ( move_uploaded_file($file['tmp_name'], $targetFilePath) ) {
                    return $path . $filename;
                }
            }
        }
        return $defaultPath;
    }

    /**
     * Sube pista de audio a servidor y retorna ruta generada.
     */
    public function uploadAudio($path, $file)
    {
        if ( !empty( basename( $file['name'] ) && str_contains( mime_content_type( $file['tmp_name'] ), 'audio' ) ) ) {
            $filename = $this->generateCode(Constants::CODE_ALPHANUMERIC, 16) . '.' . 
                pathinfo($file['name'], PATHINFO_EXTENSION);
            $targetFilePath = ROOT_PATH . $path . $filename;
            if ( move_uploaded_file($file['tmp_name'], $targetFilePath) ) {
                return $path . $filename;
            }
        }
        return false;
    }

    /**
     * Sube imagen a servidor desde aplicación móvil y retorna ruta generada.
     */
    public function uploadMobileImage($path, $imageData)
    {
        $imageName = $this->generateCode(Constants::CODE_ALPHANUMERIC, 16) . '.png';
        $targetFilePath = ROOT_PATH . $path . $imageName;
        if ( file_put_contents($targetFilePath, base64_decode($imageData)) ) {
            return $path . $imageName;
        }
        return false;
    }

    /**
     * Envía un email a través de una conexión con la librería PHPMailer.
     */
    function sendEmail($email) 
    {
        $phpMailer = new PHPMailer();

        // Propiedades de conexión SMTP

        $phpMailer->isSMTP();
        $phpMailer->Host = Constants::MAIL_HOST;
        $phpMailer->SMTPAuth = true;
        $phpMailer->Username = Constants::SENDER_EMAIL_ADDRESS;
        $phpMailer->Password = Constants::SENDER_EMAIL_PASSWORD;
        $phpMailer->Port = Constants::MAIL_PORT;
        $phpMailer->SMTPSecure = Constants::MAIL_SMTP;

        /* 
            Registro detallado de conexión SMTP: muestra por pantalla las transcripciones 
            a nivel cliente y servidor (activar solo en operaciones de depuración).
            (http://netcorecloud.com/tutorials/phpmailer-smtp-error-could-not-connect-to-smtp-host/)
        */
        // $phpMailer->SMTPDebug = 2;

        // Propiedades de email
        
        $phpMailer->CharSet = Constants::MAIL_CHARSET;
        $phpMailer->isHTML(true);
        $phpMailer->setFrom(Constants::SENDER_EMAIL_ADDRESS, Constants::SENDER_NAME);
        $phpMailer->addAddress($email->getRecipientAddress());
        $phpMailer->Subject = $email->getSubject();
        $phpMailer->Body = $email->getBody();

        if ($phpMailer->send()) {
            return true;
        } else {
            return $phpMailer->ErrorInfo;
        }
    }

    /**
     * Envía notificación push a través del servicio Firebase Cloud Messaging.
     */
    public function sendNotification($title, $message, $token)
    {
        $fields = array();
        $fields['priority'] = 'high';
        $fields['notification'] = [ 
            'title' => $title, 
            'body' => $message, 
            'data' => ['message' => $message],
            'sound' => 'default'
        ];

        if (is_array($token)){
            $fields['registration_ids'] = $token;
        } else{
            $fields['to'] = $token;
        }

        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . Constants::FCM_API
        );
                    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::FCM_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Error FCM: ' . curl_error($ch));
        }
        curl_close($ch);

        echo json_encode($result);
    }

    public function populatePodcast($numRows, $idRadio) 
    {
        $db = DatabaseConnect::getInstance();

        $cuerpo = 'Hello, World!';
        $imagen = Constants::IMG_PODCAST_PH;
        $audio = Constants::AUDIO_PATH . 'test.mp3';

        for ($i = 0; $i <= $numRows; $i++) {
            $titulo = 'Entrada ' . ($i + 1);
            $db->insertPodcast($titulo, $cuerpo, $imagen, $audio, $idRadio);
        }
    }

}
