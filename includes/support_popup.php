<?php
// includes/support_popup.php
?>
<style>
.support-fab {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
}
.support-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e40af, #84cc16);
    color: white;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s, box-shadow 0.3s;
}
.support-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}
.support-card {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 320px;
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: bottom right;
}
.support-card.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}
body.dark .support-card {
    background: #0f172a;
    border-color: #1e293b;
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    color: #f1f5f9;
}
.support-card h4 {
    margin-bottom: 0.5rem;
    font-weight: 700;
    color: #0f172a;
    font-size: 1.25rem;
}
body.dark .support-card h4 {
    color: #f1f5f9;
}
.support-card p {
    font-size: 0.95rem;
    color: #64748b;
    margin-bottom: 1.25rem;
    line-height: 1.5;
}
body.dark .support-card p {
    color: #94a3b8;
}
.support-card a {
    display: block;
    text-align: center;
    background: #1e40af;
    color: white;
    text-decoration: none;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    transition: background 0.3s;
}
.support-card a:hover {
    background: #1e3a8a;
}
</style>

<div class="support-fab">
    <div class="support-card" id="supportCard">
        <h4>Need Help?</h4>
        <p>If you have any queries, encounter issues, or need direct assistance, please feel free to reach out to us at any time!</p>
        <a href="mailto:syntalytix@gmail.com">syntalytix@gmail.com</a>
    </div>
    <button class="support-btn" onclick="toggleSupportCard()" aria-label="Toggle Support">
        💬
    </button>
</div>

<script>
function toggleSupportCard() {
    const card = document.getElementById('supportCard');
    card.classList.toggle('show');
}

// Close the popup when clicking outside of it
document.addEventListener('click', function(e) {
    const fab = document.querySelector('.support-fab');
    if (fab && !fab.contains(e.target)) {
        const card = document.getElementById('supportCard');
        if(card && card.classList.contains('show')) {
            card.classList.remove('show');
        }
    }
});
</script>
