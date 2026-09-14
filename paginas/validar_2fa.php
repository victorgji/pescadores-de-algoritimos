<?php

session_start();

if (!isset($_SESSION['2fa_usuario_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $host = "localhost";
    $user = "root";
    $db   = "projeto_de_algoritimos_ofc"; 
    $pass = "";     

    $conn = new mysqli($host, $user, $pass, $db);

    $codigo_digitado = $_POST['codigo_2fa'];
    $usuario_id = $_SESSION['2fa_usuario_id'];

    $stmt = $conn->prepare("SELECT nome, codigo_2fa, 2fa_expira FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user_data = $res->fetch_assoc();

    $agora = date('Y-m-d H:i:s');

    if ($user_data && $user_data['codigo_2fa'] === $codigo_digitado && $user_data['2fa_expira'] > $agora) {
        
        $stmt_clear = $conn->prepare("UPDATE clientes SET codigo_2fa = NULL, 2fa_expira = NULL WHERE id = ?");
        $stmt_clear->bind_param("i", $usuario_id);
        $stmt_clear->execute();

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_nome'] = $user_data['nome'];

        unset($_SESSION['2fa_usuario_id']);
        unset($_SESSION['usuario_nome_temp']);

        echo "<script>alert('Verificação concluída!'); window.location.href='../index.php';</script>";
        exit();
    } else {
        $erro = "Código inválido ou expirado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Verificação de 2 Etapas</title>
    </head>
    <body>
        <h2>Autenticação de Dois Fatores</h2>
        <p>Insira o código de 6 dígitos gerado no login.</p>
        
        <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>

        <form method="POST" action="validar_2fa.php">
            <input type="text" name="codigo_2fa" maxlength="6" placeholder="000000" required>
            <button type="submit">Verificar</button>
        </form>
    </body>
</html>