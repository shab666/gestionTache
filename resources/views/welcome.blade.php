<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau Kanban – Gestion des Tâches</title>
    <meta name="description" content="Gérez vos tâches efficacement avec un tableau Kanban interactif moderne.">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles

    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #0f0f1a;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-animated {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(99, 102, 241, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(168, 85, 247, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 70%),
                linear-gradient(135deg, #0f0f1a 0%, #13131f 50%, #0d0d18 100%);
            pointer-events: none;
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
            animation: floatOrb 12s ease-in-out infinite;
        }
        .orb-1 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(99,102,241,0.5), transparent 70%);
            top: -100px; left: -100px;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(168,85,247,0.4), transparent 70%);
            bottom: -50px; right: -50px;
            animation-delay: -4s;
        }
        .orb-3 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(59,130,246,0.3), transparent 70%);
            top: 50%; right: 20%;
            animation-delay: -8s;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translateY(0) scale(1); }
            33% { transform: translateY(-30px) scale(1.05); }
            66% { transform: translateY(20px) scale(0.95); }
        }

        /* Page content */
        .page-content {
            position: relative;
            z-index: 1;
            min-height: 100vh;
        }

        /* Header */
        .header-bar {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        }

        .header-title-text {
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            font-weight: 400;
        }

        .header-badge {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Main title section */
        .title-section {
            text-align: center;
            padding: 60px 20px 40px;
        }

        .main-title {
            font-size: clamp(28px, 5vw, 48px);
            font-weight: 800;
            color: white;
            letter-spacing: -1.5px;
            line-height: 1.1;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 0%, #c7d2fe 50%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sub-title {
            font-size: 15px;
            color: rgba(255,255,255,0.4);
            font-weight: 400;
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Animated background -->
    <div class="bg-animated"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Page content -->
    <div class="page-content">

        <!-- Header bar -->
        <header class="header-bar">
            <div class="header-logo">
                <div class="logo-icon">🗂️</div>
                <div>
                    <div class="header-title-text">TaskFlow</div>
                    <div class="header-subtitle">Gestion des tâches</div>
                </div>
            </div>
            <div class="header-badge">✦ Tableau Kanban</div>
        </header>

        <!-- Title section -->
        <div class="title-section">
            <h1 class="main-title">Mon Tableau Kanban</h1>
            <p class="sub-title">Glissez-déposez vos tâches pour organiser votre workflow en temps réel.</p>
        </div>

        <!-- Kanban board -->
        <livewire:kanban-board />

    </div>

    @livewireScripts
</body>
</html>