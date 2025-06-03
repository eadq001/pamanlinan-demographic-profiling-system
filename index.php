<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Responsive Education Site</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Poetsen+One&display=swap" rel="stylesheet">
<link rel="stylesheet" href="index.css">
<link rel="shortcut icon" href="pamanlinan.png" type="image/x-icon">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      color: #fff;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
       background-size: cover; 
       position: relative;
      
    
    }

 

    .logo {
      font-weight: 600;
      font-size: 1.5rem;
      color:rgb(24, 252, 47);
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .nav-links {
      display: flex;
      gap: 20px;
    }

    .nav-links a {
      text-decoration: none;
      color:rgb(111, 235, 229);
      transition: color 0.3s;
      font-weight:bolder;
       text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
      
    }

    .nav-links a:hover {
      color:rgb(230, 238, 4);
    }

    .hamburger {
      display: none;
      flex-direction: column;
      justify-content: space-between;
      width: 28px;
      height: 21px;
      cursor: pointer;
    }

    .hamburger span {
      background: #fff;
      height: 3px;
      width: 100%;
      border-radius: 2px;
      }

      .profile-section {
        margin-top: 120px;
        text-align: center;
        padding: 1rem;
        animation: fadeIn 1s ease;
      position: relative;
      z-index: 1; 
    }

    .profile-pic {
      border-radius: 50%;
      width: 250px;
      height: 250px;
      object-fit: cover;
      margin-bottom: 1rem;
        box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
    }

    .section-box {
      padding: 2rem;
      background: rgba(255, 255, 255, 0.05);
      margin: 1rem;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    h1 {
      color: black;
      font-size:bolder;
      color:rgb(15, 240, 240);
      margin-bottom: 10px;
      font-size:400%;
      text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);
      -webkit-text-stroke: 1px black;
      font-family: "Lilita One", sans-serif;
      font-weight: 500;
      font-style: normal;
}

 h1, h2 {
  line-height:1;
 }
    h2 {
      margin-top:-10px;
     color: black;
      font-size:bolder;
      color:rgb(7, 255, 255);
      margin-bottom: 5px;
      font-size:300%;
      text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5);
      -webkit-text-stroke: 1px black;
      font-family: "Lilita One", sans-serif;
      font-weight: 200;
      font-style: normal;
       
    }

    .section-box p {
      color: rgb(243, 239, 0);
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
    }

    .footer {
      margin-top: auto;
      padding: 1rem;
      text-align: center;
      background: #151515;
    }

    .footer a {
      color: #49f3eb;
      margin: 0 10px;
      text-decoration: none;
       
    }

    .footer a:hover {
      color: #ff9100;
     
    }
    .btn a{
      font-size:20px;
      color:rgb(219, 246, 16);
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
    }
.fancy-button {
  display: inline-block;
  text-decoration: none;
  background-color:rgb(4, 119, 9);
  color: white;
  padding: 0.35rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  border: 2px solid transparent;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  position: relative;
  overflow: hidden;
  margin: 0.5rem;
  box-shadow: 0 2px 10px rgb(0, 0, 0);
   
  
}


.fancy-button:hover::before {
  left: 0;
}

.fancy-button:hover {
  color: #fff;
  border-color:rgb(0, 246, 86);
}

.fancy-button span {
  position: relative;
  z-index: 1;
}

.overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.22); /* Green overlay */
  z-index: 0;
}



    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideDown {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        /* background-image: url('education-med-background.png'); */
        background-size: cover;
        background-repeat: no-repeat;
        padding-top: 80px;
        
      }
      .hamburger {
        display: flex;
        z-index: 1001;
      }

   
    }
  </style>
</head>
<body>
<video autoplay muted loop playsinline class="bg-video">
  <source src="pamanlinan.mp4" type="video/mp4">
</video>
 <div class="overlay"></div>
 
  <section class="profile-section">
    <img src="pamanlinan.png" alt="Profile Pic" class="profile-pic" />
    <h1>Brgy. Pamanlinan Demographic </h1>
    <h2>Profiling System</h2><br>
  <div class="btn">
  <a href="login.php" class="fancy-button">Login</a>
</div>
</section>




  <script>
    // Hamburger menu toggle
    document.querySelector('.hamburger').addEventListener('click', () => {
      document.querySelector('.nav-links').classList.toggle('active');
    });
  </script>
</body>
</html>
