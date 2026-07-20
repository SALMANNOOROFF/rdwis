<section class="trusted-companies">
  <div class="trusted-badge-container">
    <div class="trusted-badge">
      Join Over <span class="highlight">1000+</span> Companies with Tekmino Here
    </div>
  </div>

  <div class="logos-grid">
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="Flomodia" class="placeholder-logo" />
      <span class="logo-text">flomodia</span>
    </div>
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="Weglot" class="placeholder-logo" />
      <span class="logo-text">WEGLOT</span>
    </div>
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="Influence4You" class="placeholder-logo" />
      <span class="logo-text">Influence 4You</span>
    </div>
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="TSE" class="placeholder-logo" />
      <span class="logo-text">tse</span>
    </div>
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="Monceau" class="placeholder-logo" />
      <span class="logo-text">m monceau</span>
    </div>
    <div class="logo-card">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="Coudac" class="placeholder-logo" />
      <span class="logo-text">coudac</span>
    </div>
  </div>
</section>

<style>
  /* Base styles for the section */
  .trusted-companies {
    background-color: #0a0a0a; /* Dark background matching the image */
    padding: 4rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-family: 'Inter', sans-serif; /* Modern font */
  }

  /* Badge Styles */
  .trusted-badge-container {
    margin-bottom: 3rem;
  }

  .trusted-badge {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #ffffff;
    padding: 0.5rem 1.25rem;
    border-radius: 9999px; /* Pill shape */
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
  }

  .highlight {
    background-color: #c4f000; /* Neon yellow-green color */
    color: #000000;
    padding: 0.1rem 0.4rem;
    border-radius: 9999px;
    font-weight: 700;
    margin: 0 0.25rem;
  }

  /* Logos Grid Styles */
  .logos-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    max-width: 1200px;
    width: 100%;
  }

  /* Individual Logo Card */
  .logo-card {
    background-color: #171717; /* Slightly lighter than background */
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 0.75rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 180px;
    height: 80px;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .logo-card:hover {
    background-color: #222222;
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, 0.1);
  }

  /* Placeholder elements inside the card */
  .placeholder-logo {
    width: 24px;
    height: 24px;
    opacity: 0.7;
    filter: grayscale(100%);
  }

  .logo-text {
    color: #ffffff;
    font-weight: 600;
    font-size: 1.1rem;
    opacity: 0.9;
    letter-spacing: 0.05em;
  }
</style>
