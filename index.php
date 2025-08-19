<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="style.css">

  <style>
    /* Background gradient */
    body {
      background-color: #fff;
      overflow-y: hidden;
      overflow-x: hidden;
    }
    /* Gradient circles on left side */
    .gradient-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 60%;
      height: 100%;
      z-index: -1;
    }

    .circle1, .circle2 {
      position: absolute;
      border-radius: 862px;
      filter: blur(100px);
    }

    .circle1 {
      width: 700px;
      height: 700px;
      flex-shrink: 0;
      background: linear-gradient(136deg, #FFA6AB 17.46%, #5679FF 93.71%);
      top: -200px;
      left: -150px;
    }

    .circle2 {
      width: 700px;
      height: 700px;
      transform: rotate(-168.542deg);
      flex-shrink: 0;
      background: linear-gradient(135deg, #FFED98 22.87%, #5679FF 82.65%);
      top: 500px;
      right: -150px;
    }

    .hero-section {
      position: relative;
      padding: 4rem 0;
    }

    .welcome-title{
      font-size: 40px;
    }

    .welcome-sentence{
      font-size: 25px;
      margin-bottom: 40px;
    }
    .logo-pic1{
      height: 120px;
    }

    .logo-pic2{
      height: 120px;
    }

    /* Floating animation for images */
    .float-animation {
      animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-10px);
      }
    }

    /* Star spin animation */
    .spin {
      animation: spin 3.5s linear infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .slide-in {
      opacity: 0;
      transform: translateX(-50px);
      animation: slideIn 0.8s ease forwards;
    }

    /* Delay for staggered animation */
    .delay-1 { animation-delay: 0.2s; }
    .delay-2 { animation-delay: 0.6s; }
    .delay-3 { animation-delay: 1s; }
    .delay-4 { animation-delay: 1.4s; }
    .delay-5 { animation-delay: 1.8s; }

    @keyframes slideIn {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

@media (max-width: 575.98px) {
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 75vh;
      margin: 0; 
    }
    .circle2 {
      width: 300px;
      height: 300px;
      bottom: 100px;
      right: -120px;
      filter: blur(50px);
    }
    .circle1 {
      width: 350px;
      height: 300px;
      top: -100px;
      left: -100px;
      filter: blur(80px);
    }
    .welcome-title {
      font-size: 20px;
      text-align: start;
    }

    .welcome-sentence {
      font-size: 15px;
      text-align: start;
      margin-bottom: 20px;
    }

    .logo-pic1 {
      height: 80px;
    }

    .logo-pic2 {
      height: 80px;
    }

  .custom-navy-btn {
    font-size: 1rem !important;
    padding: 0.5rem 1.2rem !important;
    margin: 0 auto;
  }
  .right-column {
    display: none;
  }

  .d-flex.align-items-center.mb-4 img {
    margin-bottom: 10px;
  }

  .hero-section {
    padding: 2rem 1rem;
  }
}
/* Tablet Portrait (min-width: 576px and max-width: 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
  body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 85vh;
    margin: 0; 
  }

  .circle2 {
    width: 400px;
    height: 400px;
    bottom: 80px;
    right: -150px;
    filter: blur(70px);
  }

  .circle1 {
    width: 450px;
    height: 400px;
    top: -120px;
    left: -120px;
    filter: blur(90px);
  }

  .welcome-title {
    font-size: 26px;
    text-align: start;
  }

  .welcome-sentence {
    font-size: 18px;
    text-align: start;
    margin-bottom: 25px;
  }

  .logo-pic1 {
    height: 100px;
  }

  .logo-pic2 {
    height: 100px;
  }

  .custom-navy-btn {
    font-size: 1.1rem !important;
    padding: 0.6rem 1.5rem !important;
    margin: 0 auto;
  }

  .right-column {
    display: none; /* Hide decorative images like mobile */
  }

  .d-flex.align-items-center.mb-4 img {
    margin-bottom: 12px;
  }

  .hero-section {
    padding: 2.5rem 1.5rem;
  }
}

/* Tablet Landscape (min-width: 768px and max-width: 991.98px) */
@media (min-width: 768px) and (max-width: 991.98px) {
  body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 90vh;
    margin: 0; 
  }

  .circle2 {
    width: 500px;
    height: 500px;
    bottom: 60px;
    right: -150px;
    filter: blur(80px);
  }

  .circle1 {
    width: 550px;
    height: 500px;
    top: -130px;
    left: -130px;
    filter: blur(100px);
  }

  .welcome-title {
    font-size: 30px;
    text-align: start;
  }

  .welcome-sentence {
    font-size: 20px;
    text-align: start;
    margin-bottom: 30px;
  }

  .logo-pic1 {
    height: 100px;
  }

  .logo-pic2 {
    height: 100px;
  }

  .custom-navy-btn {
    font-size: 1.2rem !important;
    padding: 0.7rem 1.8rem !important;
    margin: 0 auto;
  }

  /* You can keep right-column visible on landscape if desired */
  .right-column {
    display: none; /* set to flex/block if you want it to show */
  }

  .d-flex.align-items-center.mb-4 img {
    margin-bottom: 14px;
  }

  .hero-section {
    padding: 3rem 2rem;
  }
}

  </style>
</head>
<body>

  <!-- Gradient Circles -->
  <div class="gradient-bg">
    <div class="circle1"></div>
    <div class="circle2"></div>
  </div>
  
  <div class="container hero-section">
    <div class="row align-items-center">
      
      <!-- Left Column -->
      <div class="col-lg-6 text-start" style="font-size: 1.25rem;">
  
    <!-- Logos -->
    <div class="d-flex align-items-center mb-5 slide-in delay-1">
      <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo" class="logo-pic1 me-3">
      <img src="admin/images/VMC School Logo.png" alt="School Logo" class="logo-pic2">
    </div>

    <!-- Title -->
    <h2 class="mb-4 welcome-title slide-in delay-2">
      <span class="highlight-pink">Shop Smart,</span>
    </h2>
    <h2 class="welcome-title slide-in delay-3">
      <span class="highlight-blue">Study Proud Montessorian!</span>
    </h2>

    <!-- Sentence -->
    <p class="mt-4 welcome-sentence slide-in delay-4">
      Equipping Villagers Montessori College with <br> Academic Essentials — One Basket at a Time.
    </p>

    <!-- Button -->
    <a href="login.php" class="text-decoration-none">
      <button class="custom-navy-btn text-decoration-none mt-5 slide-in delay-5" 
              style="font-size: 1.25rem; padding: 0.75rem 2rem;">
        Get Started
      </button>
    </a>
  </div>  

      <!-- Right Column -->
      <div class="right-column col-lg-6 position-relative">
        <img src="admin/images/circle-deco.png" class="position-absolute float-animation" style="bottom:-150px; left: -300px; width:1000px;">
        <img src="admin/images/backpack.png" alt="Backpack" class="position-absolute float-animation" style="bottom:-80px; left: 0px; width:350px;">
        <img src="admin/images/notebook.png" alt="Notebook" class="position-absolute float-animation" style="bottom: 70px; right:-20px; width:300px;">
        <img src="admin/images/eraser.png" alt="Eraser" class="position-absolute float-animation" style="top: 30px; left: 120px; width: 280px;">
        <img src="admin/images/circle-deco.png" class="position-absolute float-animation" style="top: -10px; left: 10px; width:1000px;">
        <img src="admin/images/pencil.png" alt="Pencil" class="position-absolute float-animation" style="top: -70px; right: -60px; width:200px;">
        <img src="admin/images/sharpener.png" alt="Sharpener" class="position-absolute float-animation" style="top: 100px; right:-90px; width:350px;">
        <img src="admin/images/crayons.png" alt="Crayons" class="position-absolute float-animation" style="top: 300px; left: 50px; width: 250px;">
        <img src="admin/images/star-deco.png" class="position-absolute float-animation spin" style="bottom: 300px; left: 200px; width:30px;">
        <img src="admin/images/star-deco.png" class="position-absolute float-animation spin" style="bottom: 180px; right:-20px; width:30px;">
        <img src="admin/images/star-deco.png" class="position-absolute float-animation spin" style="top: -10px; right: 230px; width:30px;">
        <img src="admin/images/star-deco.png" class="position-absolute float-animation spin" style="top: 400px; left: 650px; width:30px;">
        <img src="admin/images/star-deco.png" class="position-absolute float-animation spin" style="top: 250px; left: 250px; width:30px;">
      </div>
    </div>
  </div>

</body>
</html>
