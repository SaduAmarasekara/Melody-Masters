<?php
include 'db_connect.php';
include 'navbar.php';

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inquiry'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO inquiries (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        $message_status = "success";
    } else {
        $message_status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Studio | Melody Masters Legacy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep:     #050508;
            --bg-card:    rgba(17, 17, 24, 0.85);
            --teal:       #00e5c3;
            --teal-glow:  rgba(0, 229, 195, 0.4);
            --border:     rgba(0, 229, 195, 0.15);
            --text-muted: #5a5a72;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: #e0ddf5;
            line-height: 1.8;
            overflow-x: hidden;
        }

        /* ── AMBIENT BACKGROUND ── */
        .bg-canvas { position: fixed; inset: 0; z-index: -1; background: radial-gradient(circle at 50% -20%, #151525, var(--bg-deep)); }
        
        /* ── HERO SECTION ── */
        .hero {
            height: 70vh; display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; background: linear-gradient(rgba(5,5,8,0.3), rgba(5,5,8,1)), url('https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&q=80&w=2070');
            background-size: cover; background-position: center; background-attachment: fixed;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3.5rem, 12vw, 7rem);
            font-weight: 900; background: linear-gradient(to bottom, #fff, #777);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        /* ── LUXURY CONTENT CARDS ── */
        .wrapper { max-width: 1200px; margin: -100px auto 100px; padding: 0 20px; }

        .glass-card {
            background: var(--bg-card); backdrop-filter: blur(30px); border: 1px solid var(--border);
            border-radius: 50px; padding: 70px; margin-bottom: 50px; box-shadow: 0 50px 100px rgba(0,0,0,0.6);
        }

        .about-split { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }

        .chief-img-box {
            position: relative; width: 350px; height: 450px; margin: 0 auto;
        }

        .chief-img-box img {
            width: 100%; height: 100%; object-fit: cover; border-radius: 35px;
            border: 1px solid var(--border); filter: grayscale(30%); transition: 0.5s;
        }

        .chief-img-box:hover img { filter: grayscale(0%); transform: scale(1.02); border-color: var(--teal); }

        .curator-tag {
            position: absolute; bottom: -20px; right: -20px; background: var(--teal);
            color: #000; padding: 15px 30px; border-radius: 20px; font-weight: 800; font-size: 0.8rem;
            box-shadow: 0 10px 30px var(--teal-glow);
        }

        /* ── CONTACT GRID ── */
        .contact-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; margin-top: 50px; }

        .form-panel {
            background: rgba(255,255,255,0.02); border-radius: 40px; padding: 50px; border: 1px solid var(--border);
        }

        .field input, .field textarea {
            width: 100%; background: rgba(0,0,0,0.4); border: 1px solid var(--border);
            padding: 18px; border-radius: 20px; color: #fff; transition: 0.4s; outline: none;
        }

        .field input:focus, .field textarea:focus { border-color: var(--teal); box-shadow: 0 0 20px var(--teal-glow); }

        .btn-send {
            width: 100%; background: var(--teal); color: #000; padding: 20px; border: none;
            border-radius: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;
            cursor: pointer; transition: 0.4s; box-shadow: 0 15px 35px var(--teal-glow);
        }

        .btn-send:hover { transform: translateY(-5px); filter: brightness(1.1); }

        /* ── FLOATING CHAT SYSTEM ── */
        .chat-widget {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
        }

        .chat-button {
            width: 70px; height: 70px; background: var(--teal); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: #000;
            font-size: 1.8rem; cursor: pointer; box-shadow: 0 15px 30px var(--teal-glow);
            transition: 0.5s;
        }

        .chat-button:hover { transform: rotate(15deg) scale(1.1); }

        .chat-window {
            position: absolute; bottom: 90px; right: 0; width: 350px; background: #0f0f1a;
            border-radius: 30px; border: 1px solid var(--border); overflow: hidden;
            display: none; flex-direction: column; box-shadow: 0 30px 60px rgba(0,0,0,0.8);
        }

        .chat-header { background: var(--teal); padding: 20px; color: #000; font-weight: 800; display: flex; justify-content: space-between; }

        .chat-body { height: 300px; padding: 20px; overflow-y: auto; font-size: 0.85rem; }

        .ai-msg { background: rgba(0, 229, 195, 0.1); padding: 10px 15px; border-radius: 15px 15px 15px 0; margin-bottom: 10px; border: 1px solid var(--border); }

        .chat-input { padding: 15px; background: #050508; display: flex; gap: 10px; }

        .chat-input input { flex: 1; background: transparent; border: none; color: #fff; outline: none; }

        .tag { color: var(--teal); font-weight: 800; font-size: 0.75rem; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 15px; display: block; }

        @media (max-width: 992px) { .about-split, .contact-grid { grid-template-columns: 1fr; } .chief-img-box { width: 100%; height: 400px; } }
    </style>
</head>
<body>

    <div class="bg-canvas"></div>

    <div class="hero">
        <p>Crafting Pure Emotion</p>
        <h1>THE STUDIO</h1>
    </div>

    <div class="wrapper">
        <?php if ($message_status == "success"): ?>
            <div style="background: rgba(0, 229, 160, 0.1); border: 1px solid var(--teal); padding: 25px; border-radius: 25px; margin-bottom: 40px; text-align: center; color: var(--teal); animation: fadeInUp 0.5s ease;">
                <i class="fas fa-check-circle"></i> Message broadcasted! Our curators will reach out shortly.
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="about-split">
                <div class="chief-img-box">
                    <img src="uploads/melocontact.jpg" alt="Melody Masters Founder">
                    <div class="curator-tag">FOUNDING CURATOR</div>
                </div>
                <div>
                    <span class="tag">Our Heritage</span>
                    <h2 style="font-family: 'Playfair Display'; font-size: 3rem; margin-bottom: 20px;">Sound as Art.</h2>
                    <p>At Melody Masters, we believe instruments are not mere objects, but extensions of the human soul. Since our inception, we have curated a collection that defines excellence in audio engineering.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
                        <div style="padding: 20px; border-radius: 20px; background: rgba(0,229,195,0.05); border: 1px solid var(--border);">
                            <h4 style="color: var(--teal);">500+</h4>
                            <p style="font-size: 0.7rem; text-transform: uppercase;">Masterpieces</p>
                        </div>
                        <div style="padding: 20px; border-radius: 20px; background: rgba(0,229,195,0.05); border: 1px solid var(--border);">
                            <h4 style="color: var(--teal);">100%</h4>
                            <p style="font-size: 0.7rem; text-transform: uppercase;">Verified Gear</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-grid">
            <div class="glass-card" style="padding: 50px;">
                <span class="tag">Visit Us</span>
                <h2>The Showroom</h2>
                <div style="margin-top: 40px;">
                    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--teal); font-size: 1.5rem;"></i>
                        <p>No.200, Colombo Rd,Padukka,Sri Lanka</p>
                    </div>
                    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                        <i class="fas fa-phone-alt" style="color: var(--teal); font-size: 1.5rem;"></i>
                        <p>+94 721 134 567</p>
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <i class="fas fa-envelope" style="color: var(--teal); font-size: 1.5rem;"></i>
                        <p>info@melodymasters.com</p>
                    </div>
                </div>
            </div>

            <div class="form-panel">
                <span class="tag">Direct Signal</span>
                <h2 style="margin-bottom: 30px;">Send Inquiry</h2>
                <form method="POST">
                    <div class="field" style="margin-bottom: 20px;">
                        <input type="text" name="name" placeholder="Full Name" required>
                    </div>
                    <div class="field" style="margin-bottom: 20px;">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="field" style="margin-bottom: 20px;">
                        <input type="text" name="subject" placeholder="Subject">
                    </div>
                    <div class="field" style="margin-bottom: 30px;">
                        <textarea name="message" rows="5" placeholder="Tell us about your musical needs..." required></textarea>
                    </div>
                    <button type="submit" name="submit_inquiry" class="btn-send">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div class="chat-widget">
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <span>Melody Assistant </span>
                <i class="fas fa-times" onclick="toggleChat()" style="cursor: pointer;"></i>
            </div>
            <div class="chat-body">
                <div class="ai-msg">Greetings! How can I assist you in your musical journey today?</div>
            </div>
            <div class="chat-input">
                <input type="text" placeholder="Type a message...">
                <i class="fas fa-paper-plane" style="color: var(--teal); cursor: pointer;"></i>
            </div>
        </div>
        <div class="chat-button" onclick="toggleChat()">
            <i class="fas fa-comment-alt"></i>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function toggleChat() {
            const chat = document.getElementById('chatWindow');
            chat.style.display = chat.style.display === 'flex' ? 'none' : 'flex';
        }
    </script>
</body>
</html>