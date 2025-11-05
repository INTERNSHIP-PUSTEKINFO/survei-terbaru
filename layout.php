<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($survei) ? htmlspecialchars($survei['judul']) : 'Kuesioner Survey'; ?></title>
    <style>
        /* ============================================
           CSS RESET & BASE STYLES
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #30809C 0%, #1A4A72 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        small {
            font-size: 12px;
        }

        /* ============================================
           LAYOUT & CONTAINER
           ============================================ */
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        .form-content {
            padding: 40px 30px;
        }


        /* ============================================
           PROGRESS BAR
           ============================================ */
        .progress-bar {
            height: 4px;
            background: #e0e0e0;
            position: relative;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #30809C 0%, #1A4A72 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* ============================================
           HEADER
           ============================================ */
        .header {
            background: linear-gradient(150deg, #178fb3 0%, #0f4876 55%, #0a3358 100%);
            color: white;
            padding: 32px 36px 28px 28px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
            padding: 0;
        }

        .logo-left,
        .logo-right {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
        }

        .logo-left {
            justify-content: flex-start;
            margin-left: -8px;
        }

        .logo-right {
            justify-content: flex-end;
            margin-right: -6px;
        }

        .header-title {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: center;
            padding: 0 16px 0 24px;
            line-height: 1.3;
        }

        .header-title span {
            display: block;
            font-size: 22px;
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        .header-title span:first-child {
            font-size: 26px;
            font-weight: 700;
        }

        .header-title span:last-child {
            font-size: 22px;
            font-weight: 600;
        }

        .survey-logo {
            display: block;
            height: auto;
            width: auto;
            max-width: 100%;
        }

        .logo-left .survey-logo,
        .logo-right .survey-logo {
            height: 68px;
            width: auto;
        }

        .survey-description {
            font-size: 13px;
            opacity: 0.95;
            line-height: 1.6;
            text-align: justify;
            margin-top: 15px;
        }

        /* ============================================
           FORM CARDS & QUESTIONS
           ============================================ */
        .question-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .question-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .question-title {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        /* ============================================
           FORM ELEMENTS
           ============================================ */
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
            margin-left: 3px;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #1A4A72;
            background: white;
            box-shadow: 0 0 0 3px rgba(26, 74, 114, 0.1);
        }

        textarea {
            resize: vertical;
        }

        .radio-group,
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .radio-option,
        .checkbox-option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .radio-option:hover,
        .checkbox-option:hover {
            border-color: #1A4A72;
            background: #f0f5f8;
        }

        .radio-option input[type="radio"],
        .checkbox-option input[type="checkbox"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #1A4A72;
            flex-shrink: 0;
        }

        .radio-option label,
        .checkbox-option label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }

        /* ============================================
           RATING SLIDER
           ============================================ */
        .rating-slider-container {
            position: relative;
            margin: 30px 0 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .rating-slider-track {
            position: relative;
            height: 14px;
            background: transparent;
            border-radius: 7px;
            margin: 50px 0 30px;
            padding: 0;
        }

        .rating-slider-track::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: var(--track-width, 100%);
            height: 100%;
            background: #e0e0e0;
            border-radius: 7px;
            z-index: 0;
        }

        .rating-slider-progress {
            position: absolute;
            height: 100%;
            background: linear-gradient(to right, #30809C 0%, #1A4A72 100%);
            border-radius: 7px;
            width: 0%;
            left: 0%;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            z-index: 1;
        }

        .rating-points {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            top: 0;
            left: 0;
            z-index: 6;
            padding: 0;
            box-sizing: border-box;
        }

        .rating-point {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            flex: 1;
            height: 28px;
            cursor: pointer;
        }

        .rating-point-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: white;
            border: 3px solid #9ca3af;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            z-index: 3;
        }

        .rating-point:hover .rating-point-dot {
            transform: scale(1.15);
            border-color: #1A4A72;
            box-shadow: 0 3px 14px rgba(26, 74, 114, 0.4);
        }

        .rating-point.active .rating-point-dot {
            background: #1A4A72;
            border-color: #1A4A72;
            transform: scale(1.35);
            box-shadow: 0 4px 16px rgba(26, 74, 114, 0.5);
        }

        .rating-point-indicator {
            position: absolute;
            top: -28px;
            font-size: 20px;
            opacity: 0;
            transition: opacity 0.2s ease-out;
            pointer-events: none;
            white-space: nowrap;
            z-index: 4;
            line-height: 1;
        }

        .rating-point-emoji {
            position: absolute;
            top: -28px;
            font-size: 20px;
            opacity: 0;
            transition: opacity 0.2s ease-out;
            pointer-events: none;
            white-space: nowrap;
            z-index: 4;
            line-height: 1;
        }

        .rating-point-emoji.show {
            opacity: 1 !important;
        }

        .rating-labels {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            padding: 0;
            position: relative;
        }

        .rating-label {
            flex: 1;
            text-align: center;
            font-size: 11px;
            color: #666;
            line-height: 1.3;
            font-weight: 500;
            padding: 0;
        }

        .rating-value {
            text-align: center;
            margin-top: 20px;
            padding: 14px 20px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            color: #999;
            font-weight: 600;
            font-size: 16px;
            display: block;
            transition: all 0.3s ease;
        }

        .rating-value.selected {
            background: linear-gradient(to bottom, #f0f5f8 0%, #e6f0f5 100%);
            border: 2px solid #667eea;
            color: #1A4A72;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(26, 74, 114, 0.15);
        }

        /* ============================================
           SECTION TITLE
           ============================================ */
        .section-title {
            color: #1A4A72;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
        }

        /* ============================================
           BUTTONS
           ============================================ */
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group .submit-btn {
            flex: 1;
            margin-top: 0;
            width: 100%;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to bottom, #30809C 0%, #1A4A72 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(26, 74, 114, 0.4);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 74, 114, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-btn {
            width: 100%;
            padding: 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.5);
        }

        .back-btn:active {
            transform: translateY(0);
        }

        /* ============================================
           SUCCESS MESSAGE
           ============================================ */
        .success-message {
            display: none;
            text-align: center;
            padding: 40px;
            animation: fadeIn 0.5s ease-out;
        }

        .success-message.show {
            display: block;
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .success-message h2 {
            color: #1A4A72;
            margin-bottom: 10px;
        }

        .success-message p {
            color: #666;
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        
        /* Tablet & iPad (768px - 1024px) */
        @media (max-width: 1024px) {
            .container {
                max-width: 95%;
            }
            
            .header {
                padding: 28px 24px 26px;
            }
            
            .form-content {
                padding: 35px 25px;
            }
            
            .question-card {
                padding: 18px;
            }
        }
        
        /* Mobile & Tablet Portrait (max-width: 768px) */
        @media (max-width: 768px) {
            body {
                padding: 10px;
                align-items: flex-start;
                padding-top: 10px;
            }

            .container {
                border-radius: 15px;
                max-width: 100%;
                margin: 0;
            }

            .header {
                padding: 20px 16px 18px;
            }

            .header-container {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                padding: 0;
            }

            .logo-left,
            .logo-right {
                margin: 0;
                padding: 0;
                box-shadow: none;
                flex: 0 0 auto;
            }

            .logo-left .survey-logo,
            .logo-right .survey-logo {
                height: 45px;
            }

            .header-title {
                padding: 0 8px;
                width: auto;
                flex: 1;
                min-width: 0;
            }

            .header-title span {
                font-size: 14px;
                line-height: 1.3;
            }

            .header-title span:first-child {
                font-size: 16px;
            }
            
            .header-title span:last-child {
                font-size: 14px;
            }

            .form-content {
                padding: 25px 16px;
            }
            
            .question-card {
                padding: 16px;
                margin-bottom: 16px;
            }
            
            .question-title {
                font-size: 15px;
                margin-bottom: 12px;
            }
            
            input[type="text"],
            input[type="number"],
            select,
            textarea {
                padding: 10px 12px;
                font-size: 14px;
            }
            
            .radio-option,
            .checkbox-option {
                padding: 10px 12px;
            }
            
            .rating-slider-container {
                padding: 15px;
                margin: 20px 0 15px;
            }
            
            .rating-slider-track {
                margin: 40px 0 25px;
            }
            
            .rating-point-dot {
                width: 24px;
                height: 24px;
            }
            
            .rating-point-emoji {
                font-size: 18px;
                bottom: 30px;
            }
            
            .rating-label {
                font-size: 10px;
            }
            
            .rating-value {
                font-size: 14px;
                padding: 8px 12px;
            }

            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .submit-btn,
            .back-btn {
                padding: 12px;
                font-size: 15px;
            }
            
            .section-title {
                font-size: 16px;
                margin-bottom: 20px;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            
            .autocomplete-dropdown {
                max-height: 180px;
                font-size: 14px;
            }
            
            .autocomplete-dropdown .dropdown-item {
                padding: 10px 12px;
                font-size: 14px;
            }
        }
        
        /* Mobile Small (max-width: 480px) */
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            
            .container {
                border-radius: 12px;
            }
            
            .header {
                padding: 14px 10px 12px;
            }
            
            .header-container {
                gap: 6px;
            }
            
            .logo-left .survey-logo,
            .logo-right .survey-logo {
                height: 40px;
            }
            
            .header-title {
                padding: 0 6px;
            }
            
            .header-title span {
                font-size: 12px;
                line-height: 1.25;
            }
            
            .header-title span:first-child {
                font-size: 14px;
            }
            
            .header-title span:last-child {
                font-size: 12px;
            }
            
            .form-content {
                padding: 20px 12px;
            }
            
            .question-card {
                padding: 14px;
                margin-bottom: 14px;
            }
            
            .question-title {
                font-size: 14px;
                margin-bottom: 10px;
            }
            
            input[type="text"],
            input[type="number"],
            select,
            textarea {
                padding: 9px 10px;
                font-size: 13px;
            }
            
            .radio-option,
            .checkbox-option {
                padding: 9px 10px;
                font-size: 13px;
            }
            
            .radio-option input[type="radio"],
            .checkbox-option input[type="checkbox"] {
                width: 18px;
                height: 18px;
                margin-right: 10px;
            }
            
            .rating-slider-container {
                padding: 12px;
                margin: 15px 0 12px;
            }
            
            .rating-slider-track {
                margin: 35px 0 20px;
            }
            
            .rating-point-dot {
                width: 20px;
                height: 20px;
            }
            
            .rating-point-emoji {
                font-size: 16px;
                bottom: 25px;
            }
            
            .rating-label {
                font-size: 9px;
            }
            
            .rating-value {
                font-size: 13px;
                padding: 7px 10px;
            }
            
            .submit-btn,
            .back-btn {
                padding: 11px;
                font-size: 14px;
            }
            
            .section-title {
                font-size: 15px;
                margin-bottom: 18px;
            }
            
            .modal-content {
                width: 98%;
                margin: 2% auto;
                border-radius: 15px;
            }
            
            .modal-header {
                padding: 20px 15px;
            }
            
            .modal-body {
                padding: 20px 15px;
            }
            
            .autocomplete-dropdown {
                max-height: 150px;
                font-size: 13px;
            }
            
            .autocomplete-dropdown .dropdown-item {
                padding: 8px 10px;
                font-size: 13px;
            }
        }
        
        /* Landscape Mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 5px;
            }
            
            .container {
                max-height: 95vh;
                overflow-y: auto;
            }
            
            .header {
                padding: 15px 20px;
            }
            
            .header-container {
                flex-direction: row;
                gap: 10px;
            }
            
            .logo-left .survey-logo,
            .logo-right .survey-logo {
                height: 40px;
            }
            
            .header-title span {
                font-size: 16px;
            }
            
            .header-title span:first-child {
                font-size: 18px;
            }
            
            .form-content {
                padding: 20px 15px;
            }
        }
        
        /* Large Desktop (min-width: 1200px) */
        @media (min-width: 1200px) {
            .container {
                max-width: 700px;
            }
            
            .form-content {
                padding: 45px 35px;
            }
        }

        /* ============================================
           MODAL CAPTCHA
           ============================================ */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.5s;
        }

        .modal-header {
            background: linear-gradient(to bottom, #30809C 0%, #1A4A72 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .modal-body {
            padding: 30px;
            text-align: center;
        }

        .captcha-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }

        .captcha-image-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .captcha-image {
            display: block;
            filter: blur(0.5px);
            -webkit-filter: blur(0.5px);
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            border-radius: 4px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            pointer-events: none;
        }

        .captcha-refresh-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .captcha-refresh-btn:hover {
            background: #1A4A72;
            color: white;
            transform: rotate(180deg);
        }

        .captcha-input {
            width: 100%;
            max-width: 200px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        .captcha-input:focus {
            outline: none;
            border-color: #1A4A72;
            box-shadow: 0 0 0 3px rgba(26, 74, 114, 0.1);
        }

        .captcha-error {
            color: #e74c3c;
            margin-top: 15px;
            font-size: 14px;
            display: none;
        }

        .captcha-error.show {
            display: block;
        }

        /* ============================================
           AUTOCOMPLETE DROPDOWN
           ============================================ */
        .autocomplete-container {
            position: relative;
            z-index: 1;
        }

        .autocomplete-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 999;
            display: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 2px;
        }

        /* Pastikan card provinsi dan dropdownnya di atas kabupaten */
        #provinces_card {
            position: relative;
            z-index: 10;
        }

        #provinces_dropdown {
            z-index: 1001 !important;
            position: absolute !important;
        }

        /* Pastikan card kabupaten di bawah provinsi */
        #regencies_card {
            position: relative;
            z-index: 1;
        }

        #regencies_dropdown {
            z-index: 999;
        }

        .dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="progress-bar">
            <div class="progress-fill" id="progressBar"></div>
        </div>
        
        <div class="header">
            <!-- Header dengan logo dan judul -->
            <div class="header-container">
                <div class="logo-left">
                    <?php
                    // Gunakan path absolut untuk logo kiri juga
                    $base_path_logo_left = '';
                    if (isset($_SERVER['REQUEST_URI'])) {
                        $request_uri_left = $_SERVER['REQUEST_URI'];
                        if (strpos($request_uri_left, '/uuid/') !== false) {
                            $base_path_logo_left = strstr($request_uri_left, '/uuid/', true);
                        }
                    }
                    if (empty($base_path_logo_left)) {
                        $base_path_logo_left = '/survei-nala';
                    }
                    $logo_left_path = $base_path_logo_left . '/image/setjendprri.png';
                    ?>
                    <img src="<?php echo $logo_left_path; ?>" alt="Setjen DPR RI" class="survey-logo" onerror="this.onerror=null; this.src='<?php echo $base_path_logo_left; ?>/image/setjendprri.png';">
                </div>
                <div class="header-title">
                    <?php
                        $defaultTitleLines = ['Efisiensi Penggunaan', 'Pembayaran Digital', 'Dibandingkan Tunai'];
                        $rawTitle = isset($survei['judul']) ? $survei['judul'] : implode("\n", $defaultTitleLines);
                        $titleLines = array_filter(array_map('trim', preg_split("/\r?\n/", $rawTitle)));
                        if (empty($titleLines)) {
                            $titleLines = $defaultTitleLines;
                        }
                        foreach ($titleLines as $line) {
                            echo '<span>' . htmlspecialchars($line) . '</span>';
                        }
                    ?>
                </div>
                <div class="logo-right">
                    <?php
                    // Ambil logo dari database (kolom file atau file_name di tabel survei)
                    // Gunakan path absolut untuk menghindari masalah dengan URL UUID
                    $base_path_logo = '';
                    if (isset($_SERVER['REQUEST_URI'])) {
                        $request_uri = $_SERVER['REQUEST_URI'];
                        if (strpos($request_uri, '/uuid/') !== false) {
                            $base_path_logo = strstr($request_uri, '/uuid/', true);
                        }
                    }
                    if (empty($base_path_logo)) {
                        $base_path_logo = '/survei-nala';
                    }
                    
                    $logo_path = $base_path_logo . '/image/bkd.jpeg'; // Default logo dengan path absolut
                    if (isset($survei) && is_array($survei)) {
                        // Cek kolom 'file' dulu (jika ada)
                        if (!empty($survei['file'])) {
                            $file_path = trim($survei['file']);
                            // Jika file ada, gunakan
                            if (file_exists($file_path)) {
                                $logo_path = $base_path_logo . '/' . htmlspecialchars($file_path);
                            } elseif (file_exists('image/' . basename($file_path))) {
                                $logo_path = $base_path_logo . '/image/' . htmlspecialchars(basename($file_path));
                            } else {
                                $logo_path = $base_path_logo . '/' . htmlspecialchars($file_path);
                            }
                        }
                        // Jika tidak ada, cek kolom 'file_name'
                        elseif (!empty($survei['file_name'])) {
                            $file_name = trim($survei['file_name']);
                            // Jika file_name sudah lengkap dengan path
                            if (strpos($file_name, 'image/') === 0) {
                                $logo_path = $base_path_logo . '/' . htmlspecialchars($file_name);
                            } elseif (strpos($file_name, '/') === 0 || strpos($file_name, 'http') === 0) {
                                $logo_path = htmlspecialchars($file_name);
                            } else {
                                // Tambahkan path image/
                                $logo_path = $base_path_logo . '/image/' . htmlspecialchars($file_name);
                            }
                            // Verifikasi file benar-benar ada (cek file system, bukan URL)
                            $file_to_check = str_replace($base_path_logo . '/', '', $logo_path);
                            if (!file_exists($file_to_check)) {
                                // Fallback ke default
                                $logo_path = $base_path_logo . '/image/bkd.jpeg';
                            }
                        }
                    }
                    ?>
                    <img src="<?php echo $logo_path; ?>" alt="Logo Survei" class="survey-logo" onerror="this.onerror=null; this.src='<?php echo $base_path_logo; ?>/image/bkd.jpeg';">
                </div>
            </div>
            
            <!-- Deskripsi survei -->
            <div class="survey-description">
                Survei ini bertujuan untuk mengetahui tingkat efisiensi penggunaan pembayaran digital dibandingkan dengan transaksi tunai dalam kegiatan sehari-hari. Melalui survei ini, kami ingin memahami persepsi, kebiasaan, serta pengalaman masyarakat dalam menggunakan berbagai metode pembayaran seperti e-wallet, mobile banking, atau kartu debit/kredit.<br><br>
                Hasil survei ini akan digunakan untuk menilai sejauh mana pembayaran digital dapat meningkatkan kecepatan, kemudahan, dan keamanan transaksi dibandingkan dengan pembayaran tunai, serta faktor-faktor apa saja yang mempengaruhi preferensi pengguna.
            </div>
        </div>

        <div class="form-content" id="formContent">
            <?php echo $content; ?>
        </div>

        <div class="success-message" id="successMessage">
            <div class="success-icon">✅</div>
            <h2>Terima Kasih!</h2>
            <p>Survey Anda telah berhasil dikirim</p>
        </div>
    </div>

    <!-- Modal Captcha -->
    <div id="captchaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Verifikasi Captcha</h2>
                <p>Mohon selesaikan captcha berikut untuk mengirimkan survey Anda</p>
            </div>
            <div class="modal-body">
                <div class="captcha-container">
                    <div class="captcha-image-container">
                        <img id="captchaImage" src="captcha.php" alt="Captcha" class="captcha-image">
                        <button type="button" class="captcha-refresh-btn" id="refreshCaptchaImage" title="Refresh Captcha">🔄</button>
                    </div>
                    <input type="number" id="captchaAnswer" class="captcha-input" placeholder="Masukkan jawaban" required>
                </div>
                <div class="button-group">
                    <button type="button" class="submit-btn" id="verifyAndSubmitBtn">Verifikasi & Kirim</button>
                </div>
                <div class="captcha-error" id="captchaError">Jawaban captcha salah. Silakan coba lagi.</div>
            </div>
        </div>
    </div>

    <script>
        let currentPart = 1;
        let totalParts = document.querySelectorAll('[id^="part"]').length;
        let captchaAnswer = 0; // Variabel untuk menyimpan jawaban captcha

        function updateProgress(part) {
            let progress = (part / totalParts) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goToSection(partNum) {
            for (let i = 1; i <= totalParts; i++) {
                let part = document.getElementById('part' + i);
                if (part) part.style.display = 'none';
            }
            
            let requestedPart = document.getElementById('part' + partNum);
            if (requestedPart) {
                requestedPart.style.display = 'block';
                currentPart = partNum;
                updateProgress(partNum);
                scrollToTop();
            }
        }

        // Fungsi untuk kembali ke section sebelumnya dengan autosave (tanpa validasi required)
        function saveAndGoToSection(partNum) {
            saveSurveyData()
                .then(() => {
                    goToSection(partNum);
                })
                .catch(error => {
                    console.error('Autosave error:', error);
                    // Tetap lanjutkan ke section yang diminta meskipun autosave gagal
                    goToSection(partNum);
                });
        }

        function validateAndGoTo(partNum) {
            if (!validateCurrentPart()) {
                return;
            }

            saveSurveyData()
                .then((data) => {
                    console.log('Data saved successfully:', data);
                    goToSection(partNum);
                })
                .catch(error => {
                    console.error('Autosave error:', error);
                    // Tampilkan error message yang lebih detail
                    let errorMsg = 'Gagal menyimpan jawaban.';
                    if (error.message) {
                        errorMsg += ' ' + error.message;
                    }
                    alert(errorMsg);
                });
        }

        function validateCurrentPart() {
            let currentSection = document.getElementById('part' + currentPart);
            if (!currentSection) return true;

            let requiredInputs = currentSection.querySelectorAll('[required]');
            
            for (let input of requiredInputs) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    let name = input.name;
                    let groupChecked = document.querySelector('input[name="' + name + '"]:checked');
                    if (!groupChecked) {
                        alert('Mohon isi semua pertanyaan yang wajib diisi');
                        return false;
                    }
                } else if (input.value.trim() === '') {
                    alert('Mohon isi semua pertanyaan yang wajib diisi');
                    return false;
                }
            }
            return true;
        }

        // --- FUNGSI BARU UNTUK MENANGANI AKHIR SURVEI ---
        function validateAndShowCaptcha() {
            if (!validateCurrentPart()) {
                return;
            }

            saveSurveyData()
                .then(() => {
                    generateCaptcha();
                    document.getElementById('captchaModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Autosave error:', error);
                    alert('Gagal menyimpan data survey. Silakan coba lagi.');
                });
        }

        function saveSurveyData() {
            const formEl = document.getElementById('surveyForm');
            if (!formEl) {
                return Promise.reject(new Error('Form survei tidak ditemukan'));
            }

            const formData = new FormData(formEl);
            
            // Pastikan semua radio button yang checked (termasuk hidden untuk slider) ikut terkirim
            // FormData sudah otomatis mengambil checked radio, tapi kita pastikan semua data ada
            // Khusus untuk slider yang menggunakan hidden radio button
            const allRadioInputs = formEl.querySelectorAll('input[type="radio"]');
            allRadioInputs.forEach(input => {
                if (input.name && input.name.startsWith('kuesioner_') && input.checked) {
                    // Pastikan radio yang checked ada di FormData (termasuk yang hidden untuk slider)
                    if (!formData.has(input.name)) {
                        formData.append(input.name, input.value);
                    } else {
                        // Replace jika sudah ada dengan value yang baru (yang checked)
                        formData.set(input.name, input.value);
                    }
                }
            });
            
            // Handle checkbox (multiple values)
            const allCheckboxInputs = formEl.querySelectorAll('input[type="checkbox"]');
            const checkboxValues = {};
            allCheckboxInputs.forEach(input => {
                if (input.name && input.name.startsWith('kuesioner_') && input.checked) {
                    if (!checkboxValues[input.name]) {
                        checkboxValues[input.name] = [];
                    }
                    checkboxValues[input.name].push(input.value);
                }
            });
            // Set checkbox values
            for (let name in checkboxValues) {
                formData.delete(name); // Hapus yang lama
                checkboxValues[name].forEach(value => {
                    formData.append(name, value);
                });
            }
            
            // Handle text/textarea inputs
            const allTextInputs = formEl.querySelectorAll('input[type="text"], input[type="number"], textarea');
            allTextInputs.forEach(input => {
                if (input.name && input.name.startsWith('kuesioner_') && input.value.trim()) {
                    formData.set(input.name, input.value.trim());
                }
            });
            
            // Log semua data yang akan dikirim untuk debugging
            console.log('Saving survey data...');
            const formDataEntries = {};
            for (let [key, value] of formData.entries()) {
                if (formDataEntries[key]) {
                    // Handle multiple values (checkbox)
                    if (Array.isArray(formDataEntries[key])) {
                        formDataEntries[key].push(value);
                    } else {
                        formDataEntries[key] = [formDataEntries[key], value];
                    }
                } else {
                    formDataEntries[key] = value;
                }
            }
            console.log('Form data to send:', formDataEntries);
            
            // Log khusus untuk kuesioner
            const kuesionerData = {};
            for (let key in formDataEntries) {
                if (key.startsWith('kuesioner_')) {
                    kuesionerData[key] = formDataEntries[key];
                }
            }
            console.log('Kuesioner data:', kuesionerData);

            // Gunakan path absolut untuk menghindari masalah dengan URL UUID
            // Jika URL mengandung /uuid/, ambil base path sebelum /uuid/
            let saveUrl = 'save_survey_data.php';
            const currentPath = window.location.pathname;
            if (currentPath.includes('/uuid/')) {
                const basePath = currentPath.split('/uuid/')[0];
                saveUrl = basePath + '/save_survey_data.php';
            }

            return fetch(saveUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('HTTP error: ' + response.status + ' - ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'Gagal menyimpan data');
                    }
                    console.log('Data saved successfully:', data);
                    if (data.total_jawaban_saved !== undefined) {
                        console.log('Total jawaban saved:', data.total_jawaban_saved);
                    }
                    return data;
                })
                .catch(error => {
                    console.error('Error in saveSurveyData:', error);
                    throw error;
                });
        }

        // --- FUNGSI UNTUK CAPTCHA ---
        function generateCaptcha() {
            // Gunakan path absolut untuk menghindari masalah dengan URL UUID
            const currentPath = window.location.pathname;
            let basePath = '';
            if (currentPath.includes('/uuid/')) {
                basePath = currentPath.split('/uuid/')[0];
            }
            
            // Refresh gambar captcha dengan timestamp untuk menghindari cache
            const captchaImage = document.getElementById('captchaImage');
            if (captchaImage) {
                captchaImage.src = (basePath ? basePath + '/' : '') + 'captcha.php?t=' + new Date().getTime();
            }
            
            // Ambil jawaban dari server (dengan request terpisah)
            fetch((basePath ? basePath + '/' : '') + 'captcha.php?action=answer&t=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                captchaAnswer = data.answer; // Simpan jawaban di variabel JS
                document.getElementById('captchaAnswer').value = '';
                document.getElementById('captchaError').classList.remove('show');
            })
            .catch(error => {
                console.error('Error getting captcha answer:', error);
                // Fallback: coba ambil dari session via AJAX
                const currentPath2 = window.location.pathname;
                let basePath2 = '';
                if (currentPath2.includes('/uuid/')) {
                    basePath2 = currentPath2.split('/uuid/')[0];
                }
                fetch((basePath2 ? basePath2 + '/' : '') + 'captcha.php?action=answer')
                .then(response => response.json())
                .then(data => {
                    captchaAnswer = data.answer;
                })
                .catch(err => console.error('Error:', err));
            });
        }

        // --- AUTO-SAVE DEBOUNCE ---
        let autoSaveTimeout;
        function triggerAutoSave() {
            // Clear timeout sebelumnya jika ada
            if (autoSaveTimeout) {
                clearTimeout(autoSaveTimeout);
            }
            
            // Set timeout baru untuk debounce (simpan setelah 1 detik tidak ada perubahan)
            autoSaveTimeout = setTimeout(function() {
                saveSurveyData()
                    .catch(error => {
                        console.error('Auto-save error:', error);
                        // Tidak tampilkan alert untuk auto-save agar tidak mengganggu user
                    });
            }, 1000); // Debounce 1 detik
        }

        // --- EVENT LISTENERS ---
        document.addEventListener('DOMContentLoaded', function() {
            goToSection(1);
            updateProgress(1);

            // Setup auto-save pada semua input, textarea, radio, checkbox
            const surveyForm = document.getElementById('surveyForm');
            if (surveyForm) {
                // Event listener untuk input text dan textarea
                surveyForm.addEventListener('input', function(e) {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                        triggerAutoSave();
                    }
                });

                // Event listener untuk radio dan checkbox
                surveyForm.addEventListener('change', function(e) {
                    if (e.target.type === 'radio' || e.target.type === 'checkbox') {
                        triggerAutoSave();
                    }
                });
            }

            // Ganti event listener tombol submit akhir
            const submitFinalBtn = document.getElementById('submitFinalBtn');
            if (submitFinalBtn) {
                submitFinalBtn.onclick = validateAndShowCaptcha; // Panggil fungsi baru
            }

            // Tombol refresh captcha (gambar)
            const refreshCaptchaImageBtn = document.getElementById('refreshCaptchaImage');
            if (refreshCaptchaImageBtn) {
                refreshCaptchaImageBtn.onclick = generateCaptcha;
            }
            
            // Load captcha saat pertama kali modal dibuka
            const captchaImage = document.getElementById('captchaImage');
            if (captchaImage) {
                // Set path captcha image dengan base path yang benar
                const currentPath3 = window.location.pathname;
                let basePath3 = '';
                if (currentPath3.includes('/uuid/')) {
                    basePath3 = currentPath3.split('/uuid/')[0];
                }
                const originalSrc = captchaImage.src;
                if (originalSrc && !originalSrc.includes('http') && originalSrc.includes('captcha.php')) {
                    captchaImage.src = (basePath3 ? basePath3 + '/' : '') + 'captcha.php';
                }
                
                captchaImage.onload = function() {
                    // Ambil jawaban setelah gambar dimuat
                    fetch((basePath3 ? basePath3 + '/' : '') + 'captcha.php?action=answer&t=' + new Date().getTime())
                    .then(response => response.json())
                    .then(data => {
                        captchaAnswer = data.answer;
                    })
                    .catch(error => console.error('Error getting captcha answer:', error));
                };
            }

            // Tombol verifikasi dan kirim
            document.getElementById('verifyAndSubmitBtn').onclick = function() {
                const userAnswer = parseInt(document.getElementById('captchaAnswer').value);
                
                if (isNaN(userAnswer) || userAnswer !== captchaAnswer) {
                    document.getElementById('captchaError').classList.add('show');
                    return;
                }

                // Pastikan data survey tersimpan ke session terlebih dahulu
                // Simpan semua data form ke session sebelum submit final
                const formEl = document.getElementById('surveyForm');
                const formData = new FormData(formEl);
                
                // Pastikan semua radio button yang checked (termasuk hidden untuk slider) ikut terkirim
                const allRadioInputs = formEl.querySelectorAll('input[type="radio"]');
                allRadioInputs.forEach(input => {
                    if (input.name && input.name.startsWith('kuesioner_') && input.checked) {
                        if (!formData.has(input.name)) {
                            formData.append(input.name, input.value);
                        } else {
                            formData.set(input.name, input.value);
                        }
                    }
                });
                
                // Handle checkbox (multiple values)
                const allCheckboxInputs = formEl.querySelectorAll('input[type="checkbox"]');
                const checkboxValues = {};
                allCheckboxInputs.forEach(input => {
                    if (input.name && input.name.startsWith('kuesioner_') && input.checked) {
                        if (!checkboxValues[input.name]) {
                            checkboxValues[input.name] = [];
                        }
                        checkboxValues[input.name].push(input.value);
                    }
                });
                for (let name in checkboxValues) {
                    formData.delete(name);
                    checkboxValues[name].forEach(value => {
                        formData.append(name, value);
                    });
                }
                
                // Handle text/textarea inputs
                const allTextInputs = formEl.querySelectorAll('input[type="text"], input[type="number"], textarea');
                allTextInputs.forEach(input => {
                    if (input.name && input.name.startsWith('kuesioner_') && input.value.trim()) {
                        formData.set(input.name, input.value.trim());
                    }
                });
                
                // Gunakan path absolut untuk menghindari masalah dengan URL UUID
                const currentPath = window.location.pathname;
                let saveUrl = 'save_survey_data.php';
                if (currentPath.includes('/uuid/')) {
                    const basePath = currentPath.split('/uuid/')[0];
                    saveUrl = basePath + '/save_survey_data.php';
                }
                
                console.log('Saving survey data before submit...');
                
                // Simpan data ke session terlebih dahulu
                fetch(saveUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error('HTTP error: ' + response.status + ' - ' + text);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status !== 'success') {
                            throw new Error(data.message || 'Gagal menyimpan data');
                        }
                        console.log('Data saved successfully before submit');
                        
                        // Jika jawaban benar, kirim form ke process_survey.php
                        let processUrl = 'process_survey.php';
                        if (currentPath.includes('/uuid/')) {
                            const basePath = currentPath.split('/uuid/')[0];
                            processUrl = basePath + '/process_survey.php';
                        }
                        
                        console.log('Submitting survey to:', processUrl);
                        
                        return fetch(processUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'captcha_verified=true'
                        });
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error('HTTP error: ' + response.status + ' - ' + text);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Submit response:', data);
                        if (data.status === 'success') {
                            // Sembunyikan modal dan tampilkan pesan sukses
                            document.getElementById('captchaModal').style.display = 'none';
                            document.getElementById('formContent').style.display = 'none';
                            document.getElementById('successMessage').classList.add('show');
                            document.querySelector('.header').style.display = 'none';
                            document.querySelector('.progress-bar').style.display = 'none';
                            updateProgress(totalParts); // Set progress ke 100%
                        } else {
                            alert('Terjadi kesalahan saat mengirim survey: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting survey:', error);
                        alert('Terjadi kesalahan. Silakan coba lagi. Error: ' + error.message);
                    });
            };
        });

        // Handle "Lainnya" radio button visibility for Pekerjaan
        document.querySelectorAll('input[name="pekerjaan_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                let pekerjaanOtherInput = document.getElementById('pekerjaanLainnyaText');
                let isLainnya = this.value === '7'; // Assuming ID 7 is "Lainnya"
                
                if (pekerjaanOtherInput) {
                    if (isLainnya) {
                        pekerjaanOtherInput.style.display = 'block';
                        pekerjaanOtherInput.required = true;
                    } else {
                        pekerjaanOtherInput.style.display = 'none';
                        pekerjaanOtherInput.required = false;
                        pekerjaanOtherInput.value = '';
                    }
                }
            });
        });

        // Handle next button for part 1
        let nextBtn = document.getElementById('nextBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                validateAndGoTo(2);
            });
        }

        // Hapus garis sisa setelah titik 5 pada slider
        function adjustSliderTrackWidth() {
            document.querySelectorAll('.rating-slider-track').forEach(function(track) {
                const points = track.querySelectorAll('.rating-point');
                if (points.length > 0) {
                    const lastPoint = points[points.length - 1];
                    const trackRect = track.getBoundingClientRect();
                    const pointRect = lastPoint.getBoundingClientRect();
                    const dotCenterX = pointRect.left + pointRect.width / 2 - trackRect.left;
                    const trackWidthPercent = (dotCenterX / trackRect.width) * 100;
                    track.style.setProperty('--track-width', trackWidthPercent + '%');
                }
            });
        }

        // Jalankan setelah page load
        setTimeout(adjustSliderTrackWidth, 200);
        
        // Re-adjust saat window resize
        window.addEventListener('resize', adjustSliderTrackWidth);
    </script>
</body>
</html>