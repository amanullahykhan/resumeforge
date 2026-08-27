<style>
/* Upsell Modal Overlay */
.upsell-overlay {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.upsell-overlay.active {
    opacity: 1;
    pointer-events: all;
}
.upsell-modal {
    background: white;
    padding: 30px;
    border-radius: 16px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(20px);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    text-align: center;
}
.upsell-overlay.active .upsell-modal {
    transform: translateY(0);
}
.upsell-close {
    position: absolute;
    top: 15px; right: 15px;
    cursor: pointer;
    font-size: 20px;
    color: #64748b;
    border: none; background: none;
}
.upsell-close:hover { color: #0f172a; }
.upsell-icon {
    font-size: 48px;
    margin-bottom: 10px;
}
.upsell-title {
    font-size: 22px;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 10px;
}
.upsell-desc {
    font-size: 15px;
    color: #475569;
    margin-bottom: 25px;
    line-height: 1.5;
}
.upsell-btn {
    display: inline-block;
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 99px;
    font-weight: 600;
    text-decoration: none;
    width: 100%;
    box-sizing: border-box;
    transition: transform 0.2s, box-shadow 0.2s;
}
.upsell-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
}
</style>

<div class="upsell-overlay" id="upsellOverlay">
    <div class="upsell-modal">
        <button class="upsell-close" onclick="document.getElementById('upsellOverlay').classList.remove('active')">✕</button>
        <div class="upsell-icon">💎</div>
        <div class="upsell-title">Unlock Business Pro</div>
        <div class="upsell-desc">
            Get access to AI Resume Writing, ATS Scoring analysis, and native DOCX Word exports. 
            Stand out from the crowd with premium tools!
        </div>
        <a href="dashboard.php" class="upsell-btn">Upgrade to Pro</a>
    </div>
</div>

<script>
function triggerUpsell() {
    document.getElementById('upsellOverlay').classList.add('active');
}
</script>
