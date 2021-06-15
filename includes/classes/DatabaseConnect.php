<?php
require_once Constants::INC_EMAIL;

class DatabaseConnect
{
    private static $instance = null;
    private $util;
    private $conn;
    private $options = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => Constants::MYSQL_INIT_CMD
    ];

    private function __construct()
    {
        try {
            $this->util = new Util();
            $this->conn = new PDO(
                'mysql:host=' . Constants::DB_HOST . ';dbname=' . Constants::DB_NAME,
                Constants::DB_USER,
                Constants::DB_PASSWORD,
                $this->options
            );
        } catch (PDOException $e) {
            echo ($e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->conn = null;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DatabaseConnect();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    // VALIDAR TOKEN

    function validateToken()
    {
        if (isset($_POST['bearer-token']) && str_starts_with($_POST['bearer-token'], 'Bearer')) {

            $token = explode('Bearer ', $_POST['bearer-token'])[1];

            $subs = $this->getAllSubscribers();

            foreach ($subs as $sub) {
                if (password_verify($token, $sub['bearer_token'])) {
                    return $sub;
                }
            }
        }
        return null;
    }

    // OBTENCIÓN DE USUARIOS

    public function getUserByUsernameOrEmail($username, $email = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `usuario`
                WHERE username = :username OR email = :email LIMIT 1');

            $sth->execute( array(
                ':username' => $username,
                ':email' => isset($email) ? $email : $username
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getAdminByUsernameOrEmail($username, $email = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT u.* FROM `usuario` u INNER JOIN `admin` USING (id) 
                WHERE u.username = :username OR u.email = :email LIMIT 1');

            $sth->execute( array(
                ':username' => $username,
                ':email' => isset($email) ? $email : $username
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getSubByUsernameOrEmail($username, $email = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `usuario` u INNER JOIN `suscriptor` s USING (id) 
                WHERE (u.username = :username OR u.email = :email) AND s.activation_key IS NULL LIMIT 1');

            $sth->execute( array(
                ':username' => $username,
                ':email' => isset($email) ? $email : $username
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getAllSubscribers()
    {
        try {
            $sth = $this->conn->query('SELECT *
                FROM `usuario` u INNER JOIN `suscriptor` s USING (id) 
                LEFT JOIN `centro` c ON c.codigo = s.codigo_centro 
                ORDER BY c.denominacion, u.username');

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // COMPROBACIÓN DE USUARIO REGISTRADO

    public function isUsernameRegistered($username)
    {
        try {
            $sth = $this->conn->prepare('SELECT COUNT(*) FROM `usuario` WHERE username = :username');

            $sth->execute( array(
                ':username' => $username
            ));

            return $sth->fetchColumn();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function isEmailRegistered($email)
    {
        try {
            $sth = $this->conn->prepare('SELECT COUNT(*) FROM `usuario` WHERE email = :email');

            $sth->execute( array(
                ':email' => $email
            ));

            return $sth->fetchColumn();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    
    public function insertAdmin($username, $email)
    {
        $success = false;
        $password = $this->util->generateCode(Constants::CODE_ALPHANUMERIC, 8); // generamos contraseña aleatoria
        try {
            // Inicio de transacción (autocommit desactivado)
            $this->conn->beginTransaction();

            $sth = $this->conn->prepare('INSERT INTO `usuario` (`username`, `password`, `email`, `imagen`, `rol`) 
                VALUES (:username, :password, :email, :imagen, :role)');

            $result = $sth->execute( array(
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':email' => $email,
                ':imagen' => Constants::IMG_ADMIN_PH,
                ':role' => Constants::ADMIN
            ));

            if ($result) {
                $sth = $this->conn->prepare('INSERT INTO `admin` VALUES (:id)');

                $result = $sth->execute( array(
                    ':id' => $this->conn->lastInsertId() 
                ));

                if ($result) {
                    $url = DOMAIN_URL;
                    $emailObject = new Email(
                        $email,
                        'Credenciales de cuenta de administrador',
                        <<<HTML
                        <div>
                            <p style="padding-bottom: 15px">Estimado usuario:<p>
                            <p>Estas son sus credenciales de administrador para acceder a <a href="$url">RadioEdu</a>:</p>
                            <ul style="padding-bottom: 15px">
                                <li>Nombre de usuario: <strong>$username</strong></li>
                                <li>Contraseña: <strong>$password</strong></li>
                            </ul>
                            <p style="padding-bottom: 15px">Es aconsejable que modifique la contraseña en cuanto pueda.<p>
                            <p>Atentamente,</p>
                            <address>Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura</address>
                        </div>
                        HTML
                    );

                    $result = sendEmail($emailObject);

                    if ($result === true) {
                        // Transacción consolidada (autocommit activado)
                        $this->conn->commit();
                        $success = true;
                    } else {
                        $this->util->console_log($result);
                        $this->conn->rollBack();
                    }
                } else {
                    $this->conn->rollBack();
                }
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->util->console_log($e->getMessage());
        }
        return $success;
    }

    public function insertSubscriber($username, $password, $email, $nombre, $apellidos, $codigoCentro)
    {
        $success = false;
        try {
            // Inicio de transacción (autocommit desactivado)
            $this->conn->beginTransaction();

            $sth = $this->conn->prepare('INSERT INTO `usuario` (`username`, `password`, `email`, `imagen`, `rol`) 
                VALUES (:username, :password, :email, :imagen, :role)');

            $result = $sth->execute( array(
                ':username' => $username,
                ':password' => $password,
                ':email' => $email,
                ':imagen' => Constants::IMG_SUB_PH,
                ':role' => Constants::SUB
            ));

            if ($result) {
                $key = $this->util->generateCode(Constants::CODE_ALPHANUMERIC, Constants::TOKEN_LENGTH_LONG);

                $sth = $this->conn->prepare('INSERT INTO `suscriptor` (`id`, `activation_key`, `nombre`, `apellidos`, `codigo_centro`) 
                    VALUES (:id, :activation_key, :nombre, :apellidos, :codigoCentro)');

                $result = $sth->execute( array(
                    ':id' => $this->conn->lastInsertId(),
                    ':activation_key' => $key,
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'codigoCentro' => $codigoCentro
                ));

                if ($result) {
                    $url = DOMAIN_URL . "android/verify-key.php?key=$key";
                    $emailObject = new Email(
                        $email,
                        'Verificación de cuenta',
                        <<<HTML
                        <div>
                            <p style="padding-bottom: 15px">¡Bienvenido a RadioEdu!<p>
                            <p style="padding-bottom: 15px">Para poder activar tu cuenta es necesario que accedas 
                            al siguiente <a href="$url">enlace</a>.</p>
                            <p>Atentamente,</p>
                            <address>Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura</address>
                        </div>
                        HTML
                    );

                    $result = sendEmail($emailObject);

                    if ($result === true) {
                        // Transacción consolidada (autocommit activado)
                        $this->conn->commit();
                        $success = true;
                    } else {
                        $this->util->console_log($result);
                        $this->conn->rollBack();
                    }
                } else {
                    $this->conn->rollBack();
                }
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->util->console_log($e->getMessage());
        }
        return $success;
    }

    public function deleteSubscriber($user)
    {
        $success = false;
        try {
            // Inicio de transacción (autocommit desactivado)
            $this->conn->beginTransaction();

            $sth = $this->conn->prepare('INSERT INTO `archive` 
                (`username`, `email`, `imagen`, `nombre`, `apellidos`, `fecha_registro`, `codigo_centro`) 
                VALUES (:username, :email, :imagen, :nombre, :apellidos, :fechaRegistro, :codigoCentro)');

            $result = $sth->execute( array(
                ':username' => $user['username'],
                ':email' => $user['email'],
                ':imagen' => $user['imagen'],
                ':nombre' => $user['nombre'],
                ':apellidos' => $user['apellidos'],
                ':fechaRegistro' => $user['fecha_registro'],
                ':codigoCentro' => $user['codigo_centro']
            ));

            if ($result) {
                $sth = $this->conn->prepare('DELETE FROM `usuario` WHERE `id` = :id');

                $result = $sth->execute( array(
                    ':id' => $user['id'] 
                ));

                if ($result) {
                    // Transacción consolidada (autocommit activado)
                    $this->conn->commit();
                    $success = true;
                } else {
                    $this->conn->rollBack();
                }
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->util->console_log($e->getMessage());
        }
        return $success;
    }

    public function updateAdminProfileData($idAdmin, $username, $email, $imagen)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` u SET u.username = :username, u.email = :email, u.imagen = :imagen 
                WHERE u.id = :idAdmin');

            return $sth->execute( array(
                ':username' => $username,
                ':email' => $email,
                ':imagen' => $imagen,
                ':idAdmin' => $idAdmin
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updateSubImage($idSub, $imagen)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` SET `imagen` = :imagen WHERE `id` = :idSub');

            return $sth->execute( array(
                ':idSub' => $idSub,
                ':imagen' => $imagen
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updateSubProfileData($idSub, $username, $nombre, $apellidos, $codigoCentro)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` u INNER JOIN `suscriptor` s USING (id)  
                SET u.username = :username, s.nombre = :nombre, s.apellidos = :apellidos, s.codigo_centro = :codigoCentro 
                WHERE u.id = :idSub');

            return $sth->execute( array(
                ':username' => $username,
                ':nombre' => $nombre,
                ':apellidos' => $apellidos,
                ':codigoCentro' => $codigoCentro,
                ':idSub' => $idSub
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // COMPROBACIÓN DE CLAVE DE VERIFICACIÓN

    public function isKeyValid($key)
    {
        try {
            $sth = $this->conn->prepare('SELECT 1 FROM `suscriptor` WHERE `activation_key` = :key LIMIT 1');

            $sth->execute( array(
                ':key' => $key
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ANULACIÓN DE CLAVE DE ACTIVACIÓN

    public function nullifyKey($key)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `suscriptor` SET `activation_key` = NULL WHERE `activation_key` = :key LIMIT 1');

            return $sth->execute( array(
                ':key' => $key
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updatePassword($idUsuario, $password)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` SET `password` = :password WHERE id = :idUsuario');

            return $sth->execute( array(
                ':idUsuario' => $idUsuario,
                ':password' => password_hash($password, PASSWORD_DEFAULT)
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updateEmail($idUsuario, $email)
    {
        $success = false;
        try {
            // Inicio de transacción (autocommit desactivado)
            $this->conn->beginTransaction();

            $key = $this->util->generateCode(Constants::CODE_ALPHANUMERIC, Constants::TOKEN_LENGTH_LONG);

            $sth = $this->conn->prepare('UPDATE `usuario` u INNER JOIN `suscriptor` s USING (id)
                SET u.email = :email, s.activation_key = :key WHERE u.id = :idUsuario');

            $result = $sth->execute( array(
                ':idUsuario' => $idUsuario,
                ':email' => $email,
                ':key' => $key
            ));
            if ($result) {
                $url = DOMAIN_URL . "android/verify-key.php?key=$key";

                $emailObject = new Email(
                    $email,
                    'Verificación de cuenta',
                    <<<HTML
                    <div>
                        <p style="padding-bottom: 15px">Parece que has cambiado de correo... ¡No pasa nada!<p>
                        <p style="padding-bottom: 15px">Para poder activar tu cuenta de nuevo es necesario que accedas 
                        al siguiente <a href="$url">enlace</a>.</p>
                        <p>Atentamente,</p>
                        <address>Radio Educativa de la Consejería de Educación y Empleo de la Junta de Extremadura</address>
                    </div>
                    HTML
                );

                $result = sendEmail($emailObject);

                if ($result === true) {
                    // Transacción consolidada (autocommit activado)
                    $this->conn->commit();
                    $success = true;
                } else {
                    $this->util->console_log($result);
                    $this->conn->rollBack();
                }
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->util->console_log($e->getMessage());
        }
        return $success;
    }

    // ESTABLECIMIENTO DE CÓDIGO DE VERIFICACIÓN

    public function updateCode($idUsuario, $code)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` SET `reset_code` = :code WHERE `id` = :idUsuario');

            return $sth->execute( array(
                ':idUsuario' => $idUsuario,
                ':code' => password_hash($code, PASSWORD_DEFAULT)
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ANULACIÓN DE CODIDO DE VERIFICACIÓN

    public function nullifyCode($idUsuario)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `usuario` SET `reset_code` = NULL WHERE id = :idUsuario');

            return $sth->execute( array(
                ':idUsuario' => $idUsuario
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ESTABLECIMIENTO DE WEB TOKEN

    public function insertToken($clave, $fechaCaducidad, $idUsuario)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `webtoken` (`clave`, `fecha_caducidad`, `id_usuario`) 
                VALUES (:clave, :fechaCaducidad, :idUsuario)');

            return $sth->execute( array(
                ':clave' => $clave,
                ':fechaCaducidad' => $fechaCaducidad,
                ':idUsuario' => $idUsuario
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ANULACIÓN DE WEB TOKEN

    public function markTokenAsExpired($tokenId)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `webtoken` SET `valido` = 0 WHERE id = :id');

            return $sth->execute( array(
                ':id' => $tokenId
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // OBTENCIÓN DE WEB TOKEN

    public function getTokenByUsername($username)
    {
        try {
            $sth = $this->conn->prepare('SELECT t.* FROM `webtoken` t INNER JOIN `usuario` u ON t.id_usuario = u.id
                WHERE u.username = :username AND t.valido = 1 LIMIT 1');

            $sth->execute( array(
                ':username' => $username
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ESTABLECIMIENTO DE BEARER TOKEN

    public function updateSubBearToken($id, $token)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `suscriptor` SET `bearer_token` = :token WHERE `id` = :id');

            return $sth->execute( array(
                ':id' => $id,
                ':token' => $token
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ESTABLECIMIENTO DE FIREBASE TOKEN

    public function updateSubFbToken($id, $token)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `suscriptor` SET `fb_token` = :token WHERE `id` = :id');

            return $sth->execute( array(
                ':id' => $id,
                ':token' => $token
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // OBTENCIÓN DE FIREBASE TOKEN

    public function getFbTokenRadio($idRadio) 
    {
        try {
            $sth = $this->conn->prepare('SELECT suscriptor.fb_token 
                FROM `suscriptor` INNER JOIN `suscripcion` ON suscriptor.id = suscripcion.id_suscriptor 
                WHERE suscripcion.id_radio = :idRadio AND suscripcion.fecha_cancelacion IS NULL');

            $sth->execute( array(
                ':idRadio' => $idRadio
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // OBTENCIÓN DE DATOS DE CENTROS EDUCATIVOS

    public function getAllProvincias()
    {
        try {
            $sth = $this->conn->query('SELECT DISTINCT `provincia` FROM `centro` ORDER BY `provincia`');

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getProvinciasLibres($codigo = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT DISTINCT `provincia` FROM `centro` 
                WHERE `codigo` = :codigo OR `codigo` NOT IN 
                (SELECT `codigo_centro` FROM `radio` WHERE `codigo_centro` IS NOT NULL) 
            ORDER BY `provincia`');

            $sth->execute( array(
                ':codigo' => $codigo
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getAllLocalidades($provincia)
    {
        try {
            $sth = $this->conn->prepare('SELECT DISTINCT `localidad` FROM `centro` 
                WHERE `provincia` = :provincia ORDER BY `localidad`');

            $sth->execute( array(
                ':provincia' => $provincia
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getLocalidadesLibres($provincia, $codigo = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT DISTINCT `localidad` FROM `centro` 
                WHERE `provincia` = :provincia AND (`codigo` = :codigo OR `codigo` NOT IN 
                    (SELECT `codigo_centro` FROM `radio` WHERE `codigo_centro` IS NOT NULL))
                ORDER BY `localidad`');

            $sth->execute( array(
                ':provincia' => $provincia,
                ':codigo' => $codigo,
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getAllCentros($localidad)
    {
        try {
            $sth = $this->conn->prepare('SELECT `codigo`, `denominacion` FROM `centro` 
                WHERE `localidad` = :localidad ORDER BY `denominacion`');

            $sth->execute( array(
                ':localidad' => $localidad
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getCentrosLibres($localidad, $codigo = null)
    {
        try {
            $sth = $this->conn->prepare('SELECT `codigo`, `denominacion` FROM `centro` 
                WHERE `localidad` = :localidad AND (`codigo` = :codigo OR `codigo` NOT IN 
                    (SELECT `codigo_centro` FROM `radio` WHERE `codigo_centro` IS NOT NULL)) 
                ORDER BY `denominacion`');

            $sth->execute( array(
                ':localidad' => $localidad,
                ':codigo' => $codigo
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getCentroByCodigo($codigo)
    {
        try {
            $sth = $this->conn->prepare('SELECT `provincia`, `localidad`, `denominacion` FROM `centro` 
                WHERE `codigo` = :codigo');

            $sth->execute( array(
                ':codigo' => $codigo
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // OBTENCIÓN DE RADIOS

    public function getAllRadiosSub($idSub)
    {
        try {
            $sth = $this->conn->prepare('SELECT r.id, r.nombre, r.imagen, c.denominacion, c.localidad,
                (SELECT COUNT(*) FROM `suscripcion` s 
                WHERE s.id_radio = r.id AND s.id_suscriptor = :idSub AND s.fecha_cancelacion IS NULL) AS `suscrito`
                FROM `radio` r INNER JOIN `centro` c 
                ON r.codigo_centro = c.codigo
                WHERE r.fecha_anulacion IS NULL');

            $sth->execute( array(
                ':idSub' => $idSub
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getRadiosByAdmin()
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `radio` WHERE `id_admin` = :idAdmin AND fecha_anulacion IS NULL');

            $sth->execute( array(
                ':idAdmin' => $_SESSION['user']['id']
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getRadioById($id)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `radio` WHERE `id` = :id AND fecha_anulacion IS NULL');

            $sth->execute( array(
                ':id' => $id
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function insertRadio($nombre, $imagen, $idAdmin, $codigoCentro)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `radio` (`nombre`, `imagen`, `id_admin`, `codigo_centro`) 
                VALUES (:nombre, :imagen, :idAdmin, :codigoCentro)');
                
            return $sth->execute( array(
                ':nombre' => $nombre,
                ':imagen' => $imagen,
                ':idAdmin' => $idAdmin,
                ':codigoCentro' => $codigoCentro
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updateRadio($id, $nombre, $imagen, $codigoCentro)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `radio` 
                SET `nombre` = :nombre, `imagen` = :imagen, `codigo_centro` = :codigoCentro
                WHERE `id` = :id');

            return $sth->execute( array(
                ':nombre' => $nombre,
                ':imagen' => $imagen,
                ':codigoCentro' => $codigoCentro,
                ':id' => $id
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function deleteRadio($idAdmin, $idRadio)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `radio` r INNER JOIN `admin` a ON r.id_admin = a.id
               SET r.fecha_anulacion = NOW(), r.codigo_centro = NULL 
               WHERE r.id = :idRadio AND a.id = :idAdmin LIMIT 1');

            return $sth->execute( array(
                ':idRadio' => $idRadio,
                ':idAdmin' => $idAdmin
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // SUSCRIPCIÓN / DE-SUSCRIPCIÓN A RADIO

    public function subscribeRadio($idSub, $idRadio)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `suscripcion` (`id_suscriptor`, `id_radio`) 
                VALUES (:idSub, :idRadio)');

            return $sth->execute( array(
                ':idSub' => $idSub,
                ':idRadio' => $idRadio
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function unsubscribeRadio($idSub, $idRadio)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `suscripcion` SET `fecha_cancelacion` = NOW()
                WHERE `id_suscriptor` = :idSub AND `id_radio` = :idRadio AND `fecha_cancelacion` IS NULL LIMIT 1');

            return $sth->execute( array(
                ':idSub' => $idSub,
                ':idRadio' => $idRadio
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function unsubscribeAllRadios($idSub)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `suscripcion` SET `fecha_cancelacion` = NOW()
                WHERE `id_suscriptor` = :idSub AND `fecha_cancelacion` IS NULL');

            return $sth->execute( array(
                ':idSub' => $idSub,
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // OBTENCIÓN DE PODCASTS

    public function getNumberPodcasts($idRadio)
    {
        try {
            $sth = $this->conn->prepare('SELECT COUNT(*) FROM `podcast` 
                WHERE `id_radio` = :idRadio AND `fecha_anulacion` IS NULL');

            $sth->execute( array(
                ':idRadio' => $idRadio
            ));

            return $sth->fetchColumn();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getPodcastsByRadioId($idRadio, $offset, $recordsPerPage)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `podcast` 
                WHERE `id_radio` = :idRadio AND `fecha_anulacion` IS NULL ORDER BY `fecha_creacion` DESC
                LIMIT :offset, :recordsPerPage');

            $sth->execute( array(
                ':idRadio' => $idRadio,
                ':offset' => $offset,
                ':recordsPerPage' => $recordsPerPage
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getEntries($idRadio)
    {
        try {
            $sth = $this->conn->prepare('SELECT 
                YEAR(fecha_creacion) AS YEAR, 
                MONTH(fecha_creacion) AS MONTH,
                MONTHNAME(fecha_creacion) AS MONTHNAME,
                COUNT(*) AS TOTAL
                FROM `podcast` WHERE `id_radio` = :idRadio AND `fecha_anulacion` IS NULL 
                GROUP BY YEAR, MONTH ORDER BY fecha_creacion DESC');

            $sth->execute( array(
                ':idRadio' => $idRadio
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getPodcastsByDate($idRadio, $year, $month)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `podcast` 
                WHERE `id_radio` = :idRadio 
                AND `fecha_anulacion` IS NULL 
                AND YEAR(fecha_creacion) = :year 
                AND MONTH(fecha_creacion) = :month ORDER BY `fecha_creacion`');

            $sth->execute( array(
                ':idRadio' => $idRadio,
                ':year' => $year,
                ':month' => $month
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getPodcastsFavByRadioId($idRadio, $idSub)
    {
        try {
            $sth = $this->conn->prepare('SELECT p.*, 
                (SELECT COUNT(*) FROM `corazon` c 
                    WHERE c.id_podcast = p.id AND c.id_suscriptor = :idSub AND c.fecha_anulacion IS NULL) AS `favorito`
                FROM `podcast` p WHERE p.id_radio = :idRadio AND p.fecha_anulacion IS NULL 
                ORDER BY p.fecha_creacion DESC');

            $sth->execute( array(
                ':idRadio' => $idRadio,
                ':idSub' => $idSub
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getPodcastsFavById($id, $idSub)
    {
        try {
            $sth = $this->conn->prepare('SELECT p.*, 
                (SELECT COUNT(*) FROM `corazon` c 
                    WHERE c.id_podcast = p.id AND c.id_suscriptor = :idSub AND c.fecha_anulacion IS NULL) AS `favorito`
            FROM `podcast` p WHERE p.id = :id AND p.fecha_anulacion IS NULL LIMIT 1');

            $sth->execute( array(
                ':id' => $id,
                ':idSub' => $idSub
            ));

            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getPodcastById($id)
    {
        try {
            $sth = $this->conn->prepare('SELECT * FROM `podcast` WHERE `id` = :id AND `fecha_anulacion` IS NULL');

            $sth->execute( array(
                ':id' => $id
            ));

            return $sth->fetch();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function insertPodcast($titulo, $cuerpo, $imagen, $audio, $idRadio)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `podcast` (`titulo`, `cuerpo`, `imagen`, `audio`, `id_radio`) 
                VALUES (:titulo, :cuerpo, :imagen, :audio, :idRadio)');

            return $sth->execute( array(
                ':titulo' => $titulo,
                ':cuerpo' => $cuerpo,
                ':imagen' => $imagen,
                ':audio' => $audio,
                ':idRadio' => $idRadio
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updatePodcast($id, $titulo, $cuerpo, $imagen, $audio)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `podcast` 
                SET `titulo` = :titulo, `cuerpo` = :cuerpo, `imagen` = :imagen, `audio` = :audio 
                WHERE `id` = :id');

            return $sth->execute( array(
                ':titulo' => $titulo,
                ':cuerpo' => $cuerpo,
                ':imagen' => $imagen,
                ':audio' => $audio,
                ':id' => $id
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function deletePodcast($idAdmin, $idPodcast)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `podcast` p 
                INNER JOIN `radio` r ON p.id_radio = r.id
                INNER JOIN `admin` a ON r.id_admin = a.id
                SET p.fecha_anulacion = NOW() WHERE p.id = :idPodcast AND a.id = :idAdmin LIMIT 1');

            return $sth->execute( array(
                ':idPodcast' => $idPodcast,
                ':idAdmin' => $idAdmin
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // PODCAST FAVORITO / NO-FAVORITO

    public function likePodcast($idSub, $idPodcast)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `corazon` (`id_suscriptor`, `id_podcast`) 
                VALUES (:idSub, :idPodcast)');

            return $sth->execute( array(
                ':idSub' => $idSub,
                ':idPodcast' => $idPodcast
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function unlikePodcast($idSub, $idPodcast)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `corazon` SET `fecha_anulacion` = NOW()
                WHERE `id_suscriptor` = :idSub AND `id_podcast` = :idPodcast AND `fecha_anulacion` IS NULL LIMIT 1');

            return $sth->execute( array(
                ':idSub' => $idSub,
                ':idPodcast' => $idPodcast
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // BLOQUEO DE COMENTARIOS EN PODCAST

    public function blockPodcast($idPodcast, $state)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `podcast` SET `bloqueado` = :state WHERE `id` = :idPodcast');

            return $sth->execute( array(
                ':idPodcast' => $idPodcast,
                ':state' => $state
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // ENVÍO DE COMENTARIO

    public function insertCommentAdmin($mensaje, $idAdmin, $idPodcast)
    {
        try {
            $sth = $this->conn->prepare('INSERT INTO `comentario` (`mensaje`, `id_usuario`, `id_podcast`) 
                VALUES (:mensaje, :idAdmin, :idPodcast)');

            return $sth->execute( array(
                ':mensaje' => $mensaje,
                ':idAdmin' => $idAdmin,
                ':idPodcast' => $idPodcast
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function insertComment($mensaje, $idSub, $idPodcast)
    {
        try {
            $sth = $this->conn->prepare('SELECT `bloqueado` FROM `podcast` WHERE `id` =  :idPodcast AND `fecha_anulacion` IS NULL');

            $sth->execute( array(
                ':idPodcast' => $idPodcast
            ));

            if ($sth->fetch()['bloqueado'] == 0) {
                $sth = $this->conn->prepare('INSERT INTO `comentario` (`mensaje`, `id_usuario`, `id_podcast`) 
                    VALUES (:mensaje, :idSub, :idPodcast)');

                return $sth->execute( array(
                    ':mensaje' => $mensaje,
                    ':idSub' => $idSub,
                    ':idPodcast' => $idPodcast
                ));
            } else {
                return array('message' => 'El administrador ha bloqueado el envío de comentarios.');
            }
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function updateComment($idComentario, $mensaje)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `comentario` SET `mensaje` = :mensaje, `fecha_modificacion` = NOW() 
                WHERE `id` = :idComentario');

            return $sth->execute( array(
                ':idComentario' => $idComentario,
                ':mensaje' => $mensaje
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function deleteComment($idComentario)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `comentario` SET `fecha_anulacion` = NOW() WHERE `id` = :idComentario');

            return $sth->execute( array(
                ':idComentario' => $idComentario
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function deleteCommentByAdmin($idAdmin, $idComentario)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `comentario` c
                INNER JOIN `podcast` p ON c.id_podcast = p.id
                INNER JOIN `radio` r ON p.id_radio = r.id
                INNER JOIN `admin` a ON r.id_admin = a.id
                SET c.fecha_anulacion = NOW() WHERE c.id = :idComentario AND a.id = :idAdmin LIMIT 1');

            return $sth->execute( array(
                ':idComentario' => $idComentario,
                ':idAdmin' => $idAdmin
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    public function getCommentsByPodcast($idPodcast)
    {
        try {
            $sth = $this->conn->prepare('SELECT c.id, c.mensaje, c.fecha_registro, c.id_podcast, 
                u.id AS `id_usuario`, u.username, u.imagen, u.rol
                FROM `comentario` c INNER JOIN `usuario` u
                ON c.id_usuario = u.id 
                WHERE c.id_podcast = :idPodcast AND c.fecha_anulacion IS NULL 
                ORDER BY c.fecha_registro DESC');

            $sth->execute( array(
                ':idPodcast' => $idPodcast
            ));
            
            return $sth->fetchAll();
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // INCREMENTAR CONTADOR DE VISITAS DE PODCAST

    public function incrementViewCount($idPodcast)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `podcast` SET `visitas` = `visitas` + 1
                WHERE `id` = :idPodcast');

            return $sth->execute( array(
                ':idPodcast' => $idPodcast
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }

    // INCREMENTAR CONTADOR DE REPRODUCCIONES DE PODCAST

    public function incrementPlayCount($idPodcast)
    {
        try {
            $sth = $this->conn->prepare('UPDATE `podcast` SET `reproducciones` = `reproducciones` + 1
                WHERE `id` = :idPodcast');

            return $sth->execute( array(
                ':idPodcast' => $idPodcast
            ));
        } catch (PDOException $e) {
            $this->util->console_log($e->getMessage());
            return false;
        }
    }
}
