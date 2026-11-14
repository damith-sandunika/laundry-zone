<style>
    body { font-family: 'Outfit', sans-serif; background-color: #fcfdfd; }
    .text-brand { color: #1e3a8a; }
    .text-accent { color: #2563eb; }
    .bg-brand { background-color: #1e3a8a; }
    .bg-accent { background-color: #2563eb; }
    @keyframes wave {
        0% { transform: translateX(0); }
        50% { transform: translateX(-25%); }
        100% { transform: translateX(-50%); }
    }
    .wave-container { position: absolute; bottom: -5px; left: 0; width: 200%; height: 120px; overflow: hidden; z-index: 15; pointer-events: none; }
    .wave-layer { position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; background-repeat: repeat-x; background-position: 0 bottom; background-size: 50% 100%; animation: wave 20s linear infinite; }
    .wave-1 {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%23ffffff'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05c50.7,38.35,113.88,43.23,172.9,42.54,63.1-1.07,122.9-20.93,184-39.69,57.17-17.65,115.5-35.16,177.3-39.69,63.1-4.6,125.8,11.23,186.7,28.05,58,16,116.5,31.7,179.3,31.7,60.2,0,119.5-12.79,178.5-27.1,51.8-12.59,101.4-28.75,153.7-31.95V0Z' opacity='.5' fill='%23ffffff'/%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' fill='%23ffffff'/%3E%3C/svg%3E");
        animation-duration: 25s;
    }
    .wave-2 {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%23ffffff'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05c50.7,38.35,113.88,43.23,172.9,42.54,63.1-1.07,122.9-20.93,184-39.69,57.17-17.65,115.5-35.16,177.3-39.69,63.1-4.6,125.8,11.23,186.7,28.05,58,16,116.5,31.7,179.3,31.7,60.2,0,119.5-12.79,178.5-27.1,51.8-12.59,101.4-28.75,153.7-31.95V0Z' opacity='.5' fill='%23ffffff'/%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' fill='%23ffffff'/%3E%3C/svg%3E");
        animation-duration: 20s; animation-direction: reverse; bottom: -10px;
    }
    .hero-img-container { position: relative; z-index: 10; }
    .fade-up-enter { opacity: 0; transform: translateY(30px); transition: opacity 0.8s ease-out, transform 0.8s ease-out; }
    .fade-up-enter-active { opacity: 1; transform: translateY(0); }
    .slide-left-enter { opacity: 0; transform: translateX(-50px); transition: all 0.8s ease-out; }
    .slide-right-enter { opacity: 0; transform: translateX(50px); transition: all 0.8s ease-out; }
    .slide-active { opacity: 1; transform: translateX(0); }
    .liquid-bg { background: linear-gradient(-45deg, #0f172a, #1e3a8a, #2563eb, #0284c7); background-size: 400% 400%; animation: liquid-gradient 12s ease infinite; }
    @keyframes liquid-gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .bubbles-container { position: absolute; width: 100%; height: 100%; z-index: 5; overflow: hidden; top: 0; left: 0; }
    .bubble { position: absolute; bottom: -50px; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50%; box-shadow: inset 0 0 15px rgba(255,255,255,0.2); animation: rise infinite ease-in; }
    @keyframes rise {
        0% { bottom: -50px; transform: translateX(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 0.6; }
        100% { bottom: 110vh; transform: translateX(calc(-100px + 20vw)); opacity: 0; }
    }
    .animate-pulse-slow { animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.95; transform: scale(0.98); }
    }
</style>

