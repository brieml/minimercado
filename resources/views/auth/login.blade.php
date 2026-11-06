<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Minimercado</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 1050px;
            min-height: 620px;
            display: flex;
            background: rgba(255, 255, 255, 0.96);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.30);
        }

        /* PANEL IZQUIERDO */

        .login-info {
            width: 50%;
            padding: 60px;
            background: linear-gradient(145deg, #2563eb, #1d4ed8);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-info::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            top: -100px;
            right: -100px;
        }

        .login-info::after {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            bottom: -100px;
            left: -100px;
        }

        .brand-icon {
            width: 75px;
            height: 75px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            position: relative;
            z-index: 2;
        }

        .brand-icon svg {
            width: 52px;
            height: 52px;
        }

        .login-info h1 {
            font-size: 42px;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .login-info p {
            font-size: 17px;
            line-height: 1.7;
            color: #dbeafe;
            max-width: 430px;
            position: relative;
            z-index: 2;
        }

        .features {
            margin-top: 35px;
            position: relative;
            z-index: 2;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .feature-icon {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-icon svg {
            width: 21px;
            height: 21px;
        }

        /* PANEL LOGIN */

        .login-card {
            width: 50%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .login-header {
            margin-bottom: 35px;
        }

        .login-header h2 {
            font-size: 30px;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
        }

        .input-container {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            color: #9ca3af;
            transition: color 0.25s ease;
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
        }

        .input-container:focus-within .input-icon {
            color: #2563eb;
        }

        .form-group input {
            width: 100%;
            height: 52px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 0 15px 0 45px;
            font-size: 15px;
            outline: none;
            transition: all 0.25s ease;
            background: #f9fafb;
        }

        .form-group input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #6b7280;
        }

        .remember input {
            width: 15px;
            height: 15px;
        }

        .forgot {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.35);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #9ca3af;
            font-size: 12px;
        }

        /* RESPONSIVE */

        @media (max-width: 800px) {

            .login-container {
                max-width: 500px;
                min-height: auto;
            }

            .login-info {
                display: none;
            }

            .login-card {
                width: 100%;
                padding: 45px 35px;
            }
        }

        @media (max-width: 480px) {

            body {
                padding: 12px;
            }

            .login-card {
                padding: 35px 25px;
            }

            .login-header h2 {
                font-size: 26px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">

        <!-- PANEL INFORMATIVO -->

        <div class="login-info">

            <div class="brand-icon">
                <svg viewBox="0 0 48 48" fill="none" stroke="#1e3a8a" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <rect x="9.5" y="21" width="29" height="19.5" rx="1.2" fill="#ffffff" />
                    <path d="M20 40.5V31.5a4 4 0 0 1 8 0v9" fill="#bfdbfe" />
                    <circle cx="25.6" cy="36" r=".8" fill="#1e3a8a" stroke="none" />
                    <rect x="12.5" y="26" width="5.5" height="6.5" rx=".8" fill="#dbeafe" />
                    <rect x="30.5" y="26" width="5.5" height="6.5" rx=".8" fill="#dbeafe" />
                    <path d="M11 9h6.5l-2 9A4.25 4.25 0 0 1 7 18Z" fill="#2563eb" />
                    <path d="M17.5 9H24v9a4.25 4.25 0 0 1-8.5 0Z" fill="#dbeafe" />
                    <path d="M24 9h6.5l2 9a4.25 4.25 0 0 1-8.5 0Z" fill="#2563eb" />
                    <path d="M30.5 9H37l4 9a4.25 4.25 0 0 1-8.5 0Z" fill="#dbeafe" />
                    <path d="M5.5 40.5h37" />
                </svg>
            </div>

            <h1>Minimercado</h1>

            <p>
                Gestiona tu negocio de forma sencilla, rápida y organizada
                desde una sola plataforma.
            </p>

            <div class="features">

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <rect x="3" y="5" width="18" height="14" rx="2.2" fill="currentColor" fill-opacity=".16" />
                            <path d="M6.4 9v6M12.4 9v6M17.8 9v6" stroke-width="1.2" stroke-linecap="butt" />
                            <path d="M9.2 9v6M15 9v6" stroke-width="2.4" stroke-linecap="butt" />
                        </svg>
                    </div>
                    <span>Control de productos e inventario</span>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M6 3.5h12v17l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5-2 1.5Z" fill="currentColor"
                                fill-opacity=".16" />
                            <path d="M9 8h6M9 11.2h6M9 14.4h3.5" />
                        </svg>
                    </div>
                    <span>Gestión de ventas y operaciones</span>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M12 6.2v13.3M8 20h8M4 8.2h16" />
                            <circle cx="12" cy="4.6" r="1.3" />
                            <path d="M5 8.2 2.8 14M5 8.2 7.2 14M19 8.2 16.8 14M19 8.2 21.2 14" stroke-width="1.2" />
                            <path d="M2.2 14h5.6a2.8 2.4 0 0 1-5.6 0ZM16.2 14h5.6a2.8 2.4 0 0 1-5.6 0Z"
                                fill="currentColor" fill-opacity=".16" />
                        </svg>
                    </div>
                    <span>Información y control del negocio</span>
                </div>

            </div>

        </div>

        <!-- PANEL DE LOGIN -->

        <div class="login-card">

            <div class="login-header">

                <h2>Bienvenido</h2>

                <p>
                    Ingresa tus credenciales para acceder al sistema.
                </p>

            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">

                @csrf

                <div class="form-group">

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <div class="input-container">

                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <circle cx="12" cy="12" r="3.6" fill="currentColor" fill-opacity=".12" />
                                <path d="M15.6 8.4v4.3a2.4 2.4 0 0 0 4.8 0V12a8.4 8.4 0 1 0-3.3 6.7" />
                            </svg>
                        </span>

                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            value="{{ old('correo') }}"
                            placeholder="ejemplo@correo.com"
                            required
                            autofocus
                        >

                    </div>

                </div>

                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="input-container">

                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <circle cx="8" cy="15.5" r="3.8" fill="currentColor" fill-opacity=".12" />
                                <circle cx="8" cy="15.5" r=".9" fill="currentColor" stroke="none" />
                                <path d="M10.8 12.7 20.2 3.3M17.2 6.3l2.6 2.6M14.3 9.2l2.2 2.2" />
                            </svg>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            required
                        >

                    </div>

                </div>

                <div class="form-options">

                    <label class="remember">

                        <input type="checkbox" name="remember">

                        <span>Recordarme</span>

                    </label>

                    <a href="#" class="forgot">
                        ¿Olvidaste tu contraseña?
                    </a>

                </div>

                <button type="submit" class="login-button">
                    Iniciar sesión
                </button>

            </form>

            <div class="login-footer">
                Sistema de gestión para minimercados
            </div>

        </div>

    </div>

</body>

</html>

