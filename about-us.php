<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="h-20 bg-slate-900"></div> <!-- Header spacer -->

<!-- Hero Section -->
<section class="py-24 bg-white relative overflow-hidden">
    <div class="container mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div data-aos="fade-right">
            <span class="bg-secondary/10 text-secondary px-4 py-2 rounded-full text-sm font-bold tracking-wider uppercase mb-6 inline-block">Our Mission</span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold font-display text-slate-900 mb-8 leading-tight">
                Gurugram’s Only <span class="text-secondary">Zero-Spam</span> Real Estate Platform.
            </h1>
            <p class="text-slate-600 text-lg mb-10 leading-relaxed">
                We're tired of fake listings and endless spam calls too. Gurgaon Property Finder was built to bridge the trust gap in India's most dynamic real estate market through verification and artificial intelligence.
            </p>
            <div class="flex items-center gap-6">
                <a href="<?php echo BASE_URL; ?>/properties.php" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-bold hover:bg-secondary transition shadow-xl shadow-slate-900/10">Browse Verified Homes</a>
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-800">JD</div>
                    <div class="w-10 h-10 rounded-full border-2 border-white bg-secondary flex items-center justify-center text-xs font-bold text-white">4.9</div>
                </div>
                <span class="text-sm text-slate-500 font-medium">Trusted by 500+ Buyers</span>
            </div>
        </div>
        <div class="relative" data-aos="fade-left">
            <div class="relative rounded-[2rem] overflow-hidden shadow-2xl z-10">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Modern Gurgaon Architecture" class="w-full h-auto">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                <div class="absolute bottom-8 left-8 text-white">
                    <p class="text-3xl font-bold">100%</p>
                    <p class="text-sm opacity-80">Verified Listings</p>
                </div>
            </div>
            <!-- Decorative blur -->
            <div class="absolute -top-10 -right-10 w-64 h-64 bg-secondary/20 blur-3xl rounded-full"></div>
            <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-blue-500/10 blur-3xl rounded-full"></div>
        </div>
    </div>
</section>

<!-- Values / USPs -->
<section class="py-24 bg-slate-50">
    <div class="container mx-auto px-6 text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold font-display text-slate-900 mb-4">Why We Are Different</h2>
        <p class="text-slate-500 max-w-2xl mx-auto">Stop browsing fake listings. Start finding verified homes with AI-backed legal & investment checks.</p>
    </div>
    
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- USP 1 -->
            <div class="bg-white p-10 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:-translate-y-2 group" data-aos="fade-up">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-500">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4 font-display">Your Number is Safe</h3>
                <p class="text-slate-500 leading-relaxed">
                    Privacy is our priority. We never sell your data to random agents. Your contact info is only shared with the builders you explicitly choose to connect with.
                </p>
            </div>

            <!-- USP 2 -->
            <div class="bg-white p-10 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:-translate-y-2 group" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-500">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4 font-display">AI Legal Check</h3>
                <p class="text-slate-500 leading-relaxed">
                    Our AI models analyze builder track records, project approvals, and RERA compliance data before you even book a visit. No more hidden surprises.
                </p>
            </div>

            <!-- USP 3 -->
            <div class="bg-white p-10 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 transition hover:-translate-y-2 group" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-3xl mb-8 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-500">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4 font-display">Investment Data</h3>
                <p class="text-slate-500 leading-relaxed">
                    Make data-driven decisions. Get AI-powered rental yield forecasts and appreciation potential reports for every project in our database.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-24 bg-white">
    <div class="container mx-auto px-6 max-w-4xl">
        <div class="prose prose-lg prose-slate mx-auto">
            <h2 class="text-3xl font-bold font-display text-slate-900 mb-8 border-l-4 border-secondary pl-6">The Real Vibe Digital Media Vision</h2>
            <p class="text-slate-600 text-lg mb-6">
                Founded by the team at <strong>Real Vibe Digital Media</strong>, Gurgaon Property Finder was born out of a simple frustration: the discovery that finding a home in one of the world's fastest-growing cities had become a nightmare of spam, fake pricing, and misinformation.
            </p>
            <p class="text-slate-600 text-lg mb-6">
                We believe that buying a home is the most important financial decision of your life. It shouldn't feel like a gamble. By combining deep local expertise with cutting-edge AI technology, we provide a layer of truth that typical real estate portals lack.
            </p>
            <div class="bg-slate-900 rounded-3xl p-10 text-white my-12 shadow-2xl shadow-slate-900/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10 text-6xl">
                    <i class="fa-solid fa-quote-right"></i>
                </div>
                <p class="text-2xl italic mb-6 relative z-10">"We don't just sell properties; we sell peace of mind in a market that often lacks it."</p>
                <p class="font-bold text-secondary">— Team GGF</p>
            </div>
            <p class="text-slate-600 text-lg">
                Whether you are looking for your first home in Sector 102 or an investment opportunity on Golf Course Extension Road, our platform ensures every listing you see is verified, every legal check is done, and your privacy is always respected.
            </p>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-24">
    <div class="container mx-auto px-6">
        <div class="bg-secondary p-12 md:p-20 rounded-[3rem] text-center shadow-2xl shadow-secondary/30 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none">
                <svg width="100%" height="100%"><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/></pattern><rect width="100%" height="100%" fill="url(#grid)" /></svg>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold font-display text-white mb-8 relative z-10">Started Your Journey Yet?</h2>
            <div class="flex flex-col md:flex-row items-center justify-center gap-6 relative z-10">
                <a href="<?php echo BASE_URL; ?>/properties.php" class="bg-white text-slate-900 px-10 py-5 rounded-2xl font-bold hover:bg-slate-900 hover:text-white transition-all duration-300 w-full md:w-auto">View All Projects</a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="bg-slate-900/20 text-white border-2 border-white/30 backdrop-blur-md px-10 py-[1.1rem] rounded-2xl font-bold hover:bg-white hover:text-slate-900 transition-all duration-300 w-full md:w-auto">Contact Our Experts</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
