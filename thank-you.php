<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Gurgaon Property Finder</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b',    // Slate 800
                        secondary: '#EAB308',  // Gold
                    },
                    fontFamily: {
                        sans: ['Montserrat', 'sans-serif'],
                        display: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Montserrat', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Playfair Display', serif; }
    </style>
    
    <!-- Global site tag (gtag.js) - Google Ads: YOUR_ID_HERE -->
    <!-- You can add your Google Ads conversion script here -->
</head>
<body class="bg-stone-50 min-h-screen flex items-center justify-center">

    <div class="container mx-auto px-6 text-center">
        <div class="bg-white p-10 md:p-16 rounded-[40px] shadow-xl max-w-2xl mx-auto border border-stone-100 animate-[fadeIn_0.8s_ease-out]">
            <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="fa-solid fa-check text-4xl text-green-500"></i>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-display font-medium text-slate-900 mb-6">Thank You!</h1>
            <p class="text-xl text-slate-600 mb-8 font-light">
                We have received your enquiry. <br class="hidden md:block">
                Our property expert will contact you shortly.
            </p>
            
            <a href="index.php" class="inline-block bg-slate-900 text-white px-10 py-4 rounded-full font-bold hover:bg-secondary transition shadow-lg shadow-slate-900/20 uppercase tracking-widest text-sm">
                Back to Home
            </a>
        </div>
        
        <p class="mt-8 text-slate-400 text-sm">Gurgaon Property Finder &copy; <?php echo date('Y'); ?></p>
    </div>

</body>
</html>
