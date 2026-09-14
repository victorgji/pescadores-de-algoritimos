<?php

session_start();

$host = "localhost";
$user = "root";
$db = "projeto_de_algoritimos_ofc";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("falha na conexão: " . $conn->connect_error);
}

if (isset($_POST['entrar'])) {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM clientes WHERE email ='$email' AND (nome = '$usuario' OR telefone = '$usuario')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($senha, $row['senha'])) {

        $codigo_2fa = sprintf("%06d", mt_rand(1, 999999));
        $expira = date('y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $conn->prepare("UPDATE clientes SET codigo_2fa = ?, 2fa_expira = ? WHERE id = ?");
        $stmt->bind_param("ssi", $codigo_2fa, $expira, $row['id']);
        $stmt->execute();
        $stmt->close();

        $_SESSION['2fa_usuario_id'] = $row['id'];
        $_SESSION['usuario_nome_temp'] = $row['nome'];

        echo "<script>
                alert('Seu código 2FA de teste é: {$codigo_2fa}');
                window.location.href='../paginas/validar_2fa.php';
            </script>";
            exit();
        } else {
            echo "<script>alert('Senha incorreta.'); window.location.href='../paginas/login.html';</script>";
        }
    } 
    else {
        echo "<script>alert('Usuário ou email não encontrados.'); window.location.href='../paginas/login.html';</script>";
    }
}
$conn->close();
?>