<style>
    [x-cloak] { display: none !important; }
    body { font-family: 'Inter', sans-serif; position: relative; z-index: 1; }
    .bubbles { position: fixed; bottom: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
    .bubble { position: absolute; bottom: -100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; animation: rise infinite ease-in; }
    @keyframes rise {
        0% { bottom: -100px; transform: translateX(0); opacity: 0; }
        50% { opacity: 1; }
        100% { bottom: 1080px; transform: translateX(100px); opacity: 0; }
    }
    .bubble:nth-child(1) { width: 40px; height: 40px; left: 10%; animation-duration: 8s; }
    .bubble:nth-child(2) { width: 20px; height: 20px; left: 20%; animation-duration: 5s; animation-delay: 1s; }
    .bubble:nth-child(3) { width: 50px; height: 50px; left: 35%; animation-duration: 7s; animation-delay: 2s; }
    .bubble:nth-child(4) { width: 80px; height: 80px; left: 50%; animation-duration: 11s; animation-delay: 0s; }
    .bubble:nth-child(5) { width: 35px; height: 35px; left: 55%; animation-duration: 6s; animation-delay: 1s; }
    .bubble:nth-child(6) { width: 45px; height: 45px; left: 65%; animation-duration: 8s; animation-delay: 3s; }
    .bubble:nth-child(7) { width: 90px; height: 90px; left: 75%; animation-duration: 12s; animation-delay: 2s; }
    .bubble:nth-child(8) { width: 25px; height: 25px; left: 80%; animation-duration: 6s; animation-delay: 2s; }
</style>

