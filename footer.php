<footer class="main-footer">

    <div class="footer-eq" id="footerEQ"></div>

    <div class="footer-container">
        <div class="footer-section about">
            <h3 class="footer-logo">Melody <span>Masters</span></h3>
            <p class="footer-description">The ultimate destination for the professional musician. We curate artisanal instruments that define the next generation of sound.</p>
            <div class="social-pills">
                <a href="#" class="social-pill"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-pill"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-pill"><i class="fab fa-youtube"></i></a>
                <a href="#" class="social-pill"><i class="fab fa-x-twitter"></i></a>
            </div>
        </div>

        <div class="footer-section links">
            <h4>Curation</h4>
            <ul>
                <li><a href="index.php">The Studio</a></li>
                <li><a href="shop.php">The Showroom</a></li>
                <li><a href="register.php">Join the Elite</a></li>
                <li><a href="cart.php">Your Collection</a></li>
            </ul>
        </div>

        <div class="footer-section contact">
            <h4>Concierge</h4>
            <div class="contact-card">
                <div class="c-item">
                    <div class="c-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <p>concierge@melodymasters.com</p>
                </div>
                <div class="c-item">
                    <div class="c-icon"><i class="fas fa-phone-volume"></i></div>
                    <p>+94 112 345 678</p>
                </div>
                <div class="c-item">
                    <div class="c-icon"><i class="fas fa-map-location-dot"></i></div>
                    <p>123 Music Lane, Colombo, SL</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="bottom-wrap">
            <p>&copy; <?php echo date("Y"); ?> Melody Masters Boutique. All Rights Reserved.</p>
            <div class="legal-links">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Shipping</a>
            </div>
        </div>
    </div>
</footer>

<style>
    :root {
        --f-bg: #050508;
        --f-surface: #0a0a0f;
        --f-teal: #00e5c3;
        --f-teal-glow: rgba(0, 229, 195, 0.3);
        --f-amber: #e8c86e;
        --f-text-dim: #5a5a72;
        --f-text-light: #e0ddf5;
        --f-border: rgba(0, 229, 195, 0.1);
    }

    .main-footer {
        background: var(--f-bg);
        color: var(--f-text-dim);
        font-family: 'DM Sans', sans-serif;
        position: relative;
        padding-top: 0;
        border-top: 1px solid rgba(255,255,255,0.03);
    }

    /* ── EQ BORDER (LOGIC KEPT) ── */
    .footer-eq {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 4px;
        height: 50px;
        background: var(--f-surface);
        border-bottom: 1px solid var(--f-border);
        opacity: 0.6;
    }
    .footer-eq-bar {
        width: 4px;
        border-radius: 50px;
        background: var(--f-teal);
        animation: feqAnim linear infinite alternate;
    }
    @keyframes feqAnim { from { transform: scaleY(0.2); } to { transform: scaleY(1); } }

    .footer-container {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr 1fr;
        gap: 60px;
        max-width: 1300px;
        margin: 0 auto;
        padding: 80px 30px 40px;
    }

    /* Logo & Description */
    .footer-logo {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem;
        font-weight: 900;
        color: #fff;
        margin-bottom: 20px;
    }
    .footer-logo span { color: var(--f-teal); }
    
    .footer-description {
        font-size: 0.95rem;
        line-height: 1.8;
        max-width: 350px;
        color: var(--f-text-dim);
    }

    /* Titles */
    .footer-section h4 {
        font-family: 'Playfair Display', serif;
        color: #fff;
        font-size: 1.3rem;
        margin-bottom: 30px;
        position: relative;
    }
    .footer-section h4::after {
        content: '';
        position: absolute;
        bottom: -10px; left: 0;
        width: 30px; height: 2px;
        background: var(--f-teal);
    }

    /* Navigation */
    .footer-section ul { list-style: none; }
    .footer-section ul li { margin-bottom: 15px; }
    .footer-section ul li a {
        color: var(--f-text-dim);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .footer-section ul li a:hover { color: var(--f-teal); padding-left: 8px; }

    /* Contact Card Style */
    .contact-card {
        background: rgba(255,255,255,0.02);
        padding: 25px;
        border-radius: 20px;
        border: 1px solid var(--f-border);
    }
    .c-item { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
    .c-item:last-child { margin-bottom: 0; }
    .c-icon {
        width: 40px; height: 40px;
        background: rgba(0, 229, 195, 0.05);
        border: 1px solid var(--f-border);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--f-teal);
        font-size: 0.9rem;
        transition: 0.3s;
    }
    .c-item p { font-size: 0.85rem; color: var(--f-text-light); margin: 0; }
    .c-item:hover .c-icon { 
        background: var(--f-teal); 
        color: #000; 
        box-shadow: 0 0 15px var(--f-teal-glow);
        transform: scale(1.1);
    }

    /* Social Pills */
    .social-pills { display: flex; gap: 10px; margin-top: 30px; }
    .social-pill {
        width: 45px; height: 45px;
        background: transparent;
        border: 1.5px solid var(--f-border);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .social-pill:hover {
        background: #fff;
        color: #000;
        border-color: #fff;
        transform: translateY(-5px) rotate(10deg);
        box-shadow: 0 10px 20px rgba(255,255,255,0.1);
    }

    /* Footer Bottom */
    .footer-bottom {
        border-top: 1px solid var(--f-border);
        padding: 30px 0;
        background: #030306;
    }
    .bottom-wrap {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .legal-links { display: flex; gap: 20px; }
    .legal-links a { color: var(--f-text-dim); text-decoration: none; transition: 0.3s; }
    .legal-links a:hover { color: #fff; }

    @media (max-width: 992px) {
        .footer-container { grid-template-columns: 1fr; text-align: center; gap: 50px; }
        .footer-logo, .footer-description { margin: 0 auto 20px; }
        .footer-section h4::after { left: 50%; transform: translateX(-50%); }
        .social-pills, .bottom-wrap { justify-content: center; flex-direction: column; gap: 20px; }
        .c-item { justify-content: center; }
    }
</style>

<script>
    (function() {
        const eq = document.getElementById('footerEQ');
        if (!eq) return;
        for (let i = 0; i < 60; i++) {
            const b = document.createElement('div');
            b.className = 'footer-eq-bar';
            const h = 6 + Math.random() * 30;
            const dur = 0.4 + Math.random() * 0.8;
            b.style.cssText = `height:${h}px;animation-duration:${dur}s;animation-delay:${Math.random()}s;`;
            eq.appendChild(b);
        }
    })();
</script>