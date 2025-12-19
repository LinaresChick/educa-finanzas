<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ===== CSS ===== -->
    <link rel="stylesheet" href="/educa-finanzas/public/css/styles_login.css">
    
    <!-- ===== BOX ICONS ===== -->
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>

    <title>Iniciar Sesión - Educa Finanzas</title>
</head>
<body>
    <div class="login">
        <div class="login__content">
            <div class="login__img">
                <img src="/educa-finanzas/public/img/image.png" alt="">
            </div>

            <div class="login__forms">
                <!-- === LOGIN === -->
                <form method="post" action="index.php?controller=Auth&action=login" class="login__registre" id="login-in">
                    <h1 class="login__title">Iniciar Sesión</h1>
                    
                    <?php
                    if (!isset($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                    ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="login__box">
                        <i class='bx bx-at login__icon'></i>
                        <input type="email" id="correo" name="correo" placeholder="Correo" class="login__input" required>
                    </div>

                    <div class="login__box">
                        <i class='bx bx-lock-alt login__icon'></i>
                        <input type="password" id="clave" name="clave" placeholder="Contraseña" class="login__input" required>
                    </div>

                    <button type="submit" class="login__button">Ingresar</button>

                    <div>
                        <span class="login__account">¿No tienes cuenta?</span>
                        <span class="login__signin" id="sign-up">Regístrate</span>
                    </div>
                </form>

                <!-- === SIGN UP === -->
                <form method="post" action="index.php?controller=Auth&action=register" class="login__create none" id="login-up">
                    <h1 class="login__title">Crear Cuenta</h1>
                    
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="login__box">
                        <i class='bx bx-user login__icon'></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Usuario" class="login__input" required>
                    </div>

                    <div class="login__box">
                        <i class='bx bx-at login__icon'></i>
                        <input type="email" id="correo_registro" name="correo" placeholder="Correo" class="login__input" required>
                    </div>

                    <div class="login__box">
                        <i class='bx bx-lock-alt login__icon'></i>
                        <input type="password" id="clave_registro" name="clave" placeholder="Contraseña" class="login__input" required>
                    </div>

                    <div class="login__box">
                        <i class='bx bx-user-check login__icon'></i>
                        <select name="rol_solicitado" class="login__input" required>
                            <option value="">Seleccione un rol</option>
                            <option value="2">Administrador</option>
                            <option value="3">Director</option>
                            <option value="4">Contador</option>
                            <option value="5">Secretario</option>
                        </select>
                    </div>

                    <button type="submit" class="login__button">Registrar</button>

                    <div>
                        <span class="login__account">¿Ya tienes cuenta?</span>
                        <span class="login__signup" id="sign-in">Inicia Sesión</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--===== MAIN JS =====-->
    <script src="/educa-finanzas/public/js/main_login.js"></script>
</body>
</html>
