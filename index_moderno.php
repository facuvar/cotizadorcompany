<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cotización de Ascensores</title>
    <link rel="stylesheet" href="assets/css/modern-dark-theme.css">
    <style>
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: var(--font-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Background animation */
        .background-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.03;
            background-image: 
                linear-gradient(30deg, var(--accent-primary) 12%, transparent 12.5%, transparent 87%, var(--accent-primary) 87.5%, var(--accent-primary)),
                linear-gradient(150deg, var(--accent-primary) 12%, transparent 12.5%, transparent 87%, var(--accent-primary) 87.5%, var(--accent-primary)),
                linear-gradient(30deg, var(--accent-primary) 12%, transparent 12.5%, transparent 87%, var(--accent-primary) 87.5%, var(--accent-primary)),
                linear-gradient(150deg, var(--accent-primary) 12%, transparent 12.5%, transparent 87%, var(--accent-primary) 87.5%, var(--accent-primary)),
                linear-gradient(60deg, var(--accent-primary-dark) 25%, transparent 25.5%, transparent 75%, var(--accent-primary-dark) 75%, var(--accent-primary-dark)),
                linear-gradient(60deg, var(--accent-primary-dark) 25%, transparent 25.5%, transparent 75%, var(--accent-primary-dark) 75%, var(--accent-primary-dark));
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
            animation: backgroundShift 20s ease infinite;
        }

        @keyframes backgroundShift {
            0% { background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px; }
            100% { background-position: 80px 140px, 80px 140px, 120px 210px, 120px 210px, 80px 140px, 120px 210px; }
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: var(--spacing-xl);
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header-section {
            text-align: center;
            margin-bottom: var(--spacing-xl) * 2;
        }

        .logo-container {
            width: 100px;
            height: 100px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-lg);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .main-title {
            font-size: var(--text-3xl);
            font-weight: 700;
            margin-bottom: var(--spacing-md);
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: var(--text-lg);
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Options grid */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
        }

        .option-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 300px;
        }

        .option-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: var(--gradient-primary);
            border-radius: var(--radius-lg);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .option-card:hover::before {
            opacity: 1;
        }

        .option-icon {
            width: 80px;
            height: 80px;
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--spacing-lg);
            font-size: 2.5rem;
        }

        .option-card.cotizador .option-icon {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-primary);
        }

        .option-card.admin .option-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
        }

        .option-title {
            font-size: var(--text-xl);
            font-weight: 600;
            margin-bottom: var(--spacing-md);
        }

        .option-description {
            color: var(--text-secondary);
            margin-bottom: var(--spacing-lg);
            flex: 1;
        }

        .option-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .option-features li {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) 0;
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .option-features li::before {
            content: '✓';
            color: var(--accent-success);
            font-weight: bold;
        }

        .option-cta {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
            color: var(--accent-primary);
            font-weight: 500;
            transition: gap 0.3s ease;
        }

        .option-card:hover .option-cta {
            gap: var(--spacing-md);
        }

        /* Info section */
        .info-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            text-align: center;
            margin-top: var(--spacing-xl) * 2;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-xl);
            margin-top: var(--spacing-xl);
        }

        .info-item {
            text-align: center;
        }

        .info-value {
            font-size: var(--text-3xl);
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: var(--spacing-xs);
        }

        .info-label {
            font-size: var(--text-sm);
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: var(--spacing-xl) * 3;
            padding: var(--spacing-lg);
            color: var(--text-muted);
            font-size: var(--text-sm);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .main-title {
                font-size: var(--text-2xl);
            }
            
            .subtitle {
                font-size: var(--text-base);
            }
        }

        /* Floating elements */
        .floating-element {
            position: fixed;
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            opacity: 0.1;
            animation: floatRandom 20s infinite ease-in-out;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 25s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 5s;
            animation-duration: 30s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
            animation-duration: 35s;
        }

        @keyframes floatRandom {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(40px, 10px) scale(1.05); }
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>

    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <div class="logo-container">
                <span id="logo-icon" style="color: white; font-size: 3rem;"></span>
            </div>
            <h1 class="main-title">Sistema de Cotización de Ascensores</h1>
            <p class="subtitle">Genera presupuestos profesionales en minutos con nuestra plataforma moderna y eficiente</p>
        </div>

        <!-- Options -->
        <div class="options-grid">
            <!-- Cotizador -->
            <a href="cotizador_moderno.php" class="option-card cotizador">
                <div class="option-icon">
                    <span id="calc-icon"></span>
                </div>
                <h2 class="option-title">Cotizador Online</h2>
                <p class="option-description">Crea presupuestos personalizados de forma rápida y sencilla. Ideal para clientes y vendedores.</p>
                <ul class="option-features">
                    <li>Interfaz intuitiva y moderna</li>
                    <li>Cálculo automático de precios</li>
                    <li>Generación de PDF instantánea</li>
                    <li>Sin necesidad de registro</li>
                </ul>
                <span class="option-cta">
                    Comenzar a cotizar
                    <span id="arrow-cotizador"></span>
                </span>
            </a>

            <!-- Admin -->
            <a href="admin/index_moderno.php" class="option-card admin">
                <div class="option-icon">
                    <span id="admin-icon"></span>
                </div>
                <h2 class="option-title">Panel de Administración</h2>
                <p class="option-description">Gestiona productos, precios y presupuestos. Acceso exclusivo para administradores.</p>
                <ul class="option-features">
                    <li>Dashboard con estadísticas</li>
                    <li>Gestión de productos y precios</li>
                    <li>Historial de presupuestos</li>
                    <li>Importación desde Excel</li>
                </ul>
                <span class="option-cta">
                    Acceder al panel
                    <span id="arrow-admin"></span>
                </span>
            </a>
        </div>

        <!-- Info section -->
        <div class="info-section">
            <h3 style="font-size: var(--text-xl); margin-bottom: var(--spacing-md);">
                ¿Por qué elegir nuestro sistema?
            </h3>
            <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
                Diseñado específicamente para la industria de ascensores, con todas las características que necesitas
            </p>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-value">100%</div>
                    <div class="info-label">Personalizable</div>
                </div>
                <div class="info-item">
                    <div class="info-value">24/7</div>
                    <div class="info-label">Disponible</div>
                </div>
                <div class="info-item">
                    <div class="info-value">€0</div>
                    <div class="info-label">Sin costos ocultos</div>
                </div>
                <div class="info-item">
                    <div class="info-value">5min</div>
                    <div class="info-label">Tiempo promedio</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2024 Sistema de Cotización de Ascensores. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="assets/js/modern-icons.js"></script>
    <script>
        // Cargar iconos
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('logo-icon').innerHTML = modernUI.getIcon('chart');
            document.getElementById('calc-icon').innerHTML = modernUI.getIcon('cart', 'icon-lg');
            document.getElementById('admin-icon').innerHTML = modernUI.getIcon('settings', 'icon-lg');
            document.getElementById('arrow-cotizador').innerHTML = modernUI.getIcon('arrowRight');
            document.getElementById('arrow-admin').innerHTML = modernUI.getIcon('arrowRight');
        });
    </script>
</body>
</html> 