<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Pricing Hero Section -->
<section class="relative pt-32 pb-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, gold 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Glow Effects -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl"></div>
    
    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center max-w-3xl mx-auto" data-aos="fade-up">
            <!-- Launch Badge -->
            <div class="inline-flex items-center gap-2 bg-gradient-to-r from-secondary to-yellow-500 text-white px-6 py-2 rounded-full text-sm font-bold uppercase tracking-wider mb-6 animate-pulse">
                <i class="fa-solid fa-rocket"></i> Grand Launch Offer
            </div>
            
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 font-display">
                Membership Plans
            </h1>
            <p class="text-xl text-slate-300 mb-4">
                List unlimited properties absolutely <span class="text-secondary font-bold">FREE</span> during our launch period!
            </p>
            <p class="text-slate-400 text-lg">
                All premium features unlocked. No hidden charges. Limited time offer.
            </p>
        </div>
    </div>
</section>

<!-- Pricing Cards Section -->
<section class="py-20 bg-gradient-to-b from-slate-50 to-white relative">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-7xl mx-auto">
            
            <!-- SILVER PLAN -->
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group" data-aos="fade-up" data-aos-delay="0">
                <div class="p-8 pb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900 font-display">Silver</h3>
                            <p class="text-slate-500 text-sm mt-1">Perfect for Newcomers</p>
                        </div>
                        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center">
                            <i class="fa-solid fa-medal text-slate-600 text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Pricing -->
                    <div class="mb-6">
                        <div class="flex items-baseline gap-3 mb-2">
                            <span class="text-5xl font-black text-slate-900">₹0</span>
                            <span class="text-slate-500 text-sm font-medium">/month</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 line-through text-lg">₹6,000</span>
                            <span class="bg-red-100 text-red-700 px-3 py-0.5 rounded-full text-xs font-bold uppercase">100% OFF</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">🎉 Free during launch period</p>
                    </div>
                    
                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Up to <strong>10 property listings</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span><strong>2 Featured</strong> listings/month</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Basic analytics dashboard</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Email support</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Property listing validity: <strong>90 days</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Auto-refresh listings</span>
                        </li>
                    </ul>
                    
                    <a href="register.php?plan=silver" class="block w-full text-center bg-slate-900 text-white py-3.5 rounded-lg font-bold hover:bg-slate-800 transition-all text-sm uppercase tracking-wide shadow-lg hover:shadow-xl active:scale-95">
                        Get Started Free
                    </a>
                </div>
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-100">
                    <p class="text-xs text-slate-600 text-center">
                        <i class="fa-solid fa-clock mr-1"></i> Instant activation
                    </p>
                </div>
            </div>

            <!-- GOLD PLAN (POPULAR) -->
            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl overflow-hidden border-2 border-secondary shadow-2xl hover:shadow-secondary/20 transition-all duration-300 hover:-translate-y-2 group relative scale-105" data-aos="fade-up" data-aos-delay="100">
                <!-- Popular Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                    <div class="bg-gradient-to-r from-secondary to-yellow-500 text-white px-6 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                        <i class="fa-solid fa-star mr-1"></i> Most Popular
                    </div>
                </div>
                
                <div class="p-8 pb-6 pt-10">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900 font-display">Gold</h3>
                            <p class="text-slate-600 text-sm mt-1">For Professional Brokers</p>
                        </div>
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-secondary to-yellow-500 flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-crown text-white text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Pricing -->
                    <div class="mb-6">
                        <div class="flex items-baseline gap-3 mb-2">
                            <span class="text-5xl font-black text-slate-900">₹0</span>
                            <span class="text-slate-600 text-sm font-medium">/month</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 line-through text-lg">₹30,000</span>
                            <span class="bg-red-100 text-red-700 px-3 py-0.5 rounded-full text-xs font-bold uppercase">100% OFF</span>
                        </div>
                        <p class="text-xs text-slate-600 mt-2 font-medium">🎉 Premium features absolutely free!</p>
                    </div>
                    
                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span><strong>Unlimited property listings</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span><strong>10 Featured</strong> listings/month</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Advanced analytics & insights</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Priority email & phone support</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Property validity: <strong>180 days</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Premium "Gold Badge" on listings</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Lead management CRM</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-secondary text-lg mt-0.5 flex-shrink-0"></i>
                            <span>AI Investment Reports</span>
                        </li>
                    </ul>
                    
                    <a href="register.php?plan=gold" class="block w-full text-center bg-gradient-to-r from-secondary to-yellow-500 text-white py-4 rounded-lg font-bold hover:shadow-xl transition-all text-sm uppercase tracking-wide shadow-lg active:scale-95">
                        Get Started Free <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
                <div class="bg-gradient-to-r from-yellow-100 to-orange-100 px-8 py-4 border-t-2 border-secondary/20">
                    <p class="text-xs text-slate-700 text-center font-medium">
                        <i class="fa-solid fa-bolt mr-1 text-secondary"></i> No credit card required
                    </p>
                </div>
            </div>

            <!-- DIAMOND PLAN -->
            <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group" data-aos="fade-up" data-aos-delay="200">
                <div class="p-8 pb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900 font-display">Diamond</h3>
                            <p class="text-slate-500 text-sm mt-1">For Enterprise Agencies</p>
                        </div>
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-gem text-white text-2xl"></i>
                        </div>
                    </div>
                    
                    <!-- Pricing -->
                    <div class="mb-6">
                        <div class="flex items-baseline gap-3 mb-2">
                            <span class="text-5xl font-black text-slate-900">₹0</span>
                            <span class="text-slate-500 text-sm font-medium">/month</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 line-through text-lg">₹35,000</span>
                            <span class="bg-red-100 text-red-700 px-3 py-0.5 rounded-full text-xs font-bold uppercase">100% OFF</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">🎉 Elite features at zero cost</p>
                    </div>
                    
                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span><strong>Unlimited property listings</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span><strong>Unlimited Featured</strong> listings</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Enterprise-grade analytics</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Dedicated account manager</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Property validity: <strong>365 days</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Exclusive "Diamond Badge"</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Advanced CRM + API access</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Custom branding options</span>
                        </li>
                        <li class="flex items-start gap-3 text-slate-700">
                            <i class="fa-solid fa-circle-check text-cyan-500 text-lg mt-0.5 flex-shrink-0"></i>
                            <span>Priority listing placement</span>
                        </li>
                    </ul>
                    
                    <a href="register.php?plan=diamond" class="block w-full text-center bg-gradient-to-r from-cyan-500 to-blue-500 text-white py-3.5 rounded-lg font-bold hover:shadow-xl transition-all text-sm uppercase tracking-wide shadow-lg active:scale-95">
                        Get Started Free
                    </a>
                </div>
                <div class="bg-cyan-50 px-8 py-4 border-t border-cyan-100">
                    <p class="text-xs text-slate-700 text-center">
                        <i class="fa-solid fa-headset mr-1"></i> 24/7 premium support included
                    </p>
                </div>
            </div>
            
        </div>

        <!-- Comparison Note -->
        <div class="mt-16 max-w-4xl mx-auto" data-aos="fade-up">
            <div class="bg-gradient-to-r from-secondary/10 to-cyan-500/10 border-2 border-secondary/20 rounded-2xl p-8">
                <div class="text-center">
                    <i class="fa-solid fa-info-circle text-secondary text-3xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">Limited Time Launch Offer</h3>
                    <p class="text-slate-700 text-lg mb-4">
                        All membership plans are <span class="text-secondary font-bold">100% FREE</span> during our grand launch period. 
                        Get unlimited access to premium features at zero cost!
                    </p>
                    <p class="text-slate-600 text-sm">
                        No credit card required • No hidden fees • Cancel anytime
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-slate-50">
    <div class="container mx-auto px-6">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-slate-900 mb-4 font-display">Frequently Asked Questions</h2>
                <p class="text-slate-600">Everything you need to know about our pricing</p>
            </div>
            
            <div class="space-y-4">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100" data-aos="fade-up">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Is it really free?</h3>
                    <p class="text-slate-600">Yes! During our launch period, all plans are 100% free with full access to premium features. No credit card required.</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100" data-aos="fade-up" data-aos-delay="50">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">How long is the free period?</h3>
                    <p class="text-slate-600">The launch offer is available for early adopters. We'll notify you well in advance before introducing any charges.</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Can I upgrade or downgrade later?</h3>
                    <p class="text-slate-600">Absolutely! You can change your plan anytime based on your business needs.</p>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100" data-aos="fade-up" data-aos-delay="150">
                    <h3 class="text-lg font-bold text-slate-900 mb-2">What happens after the launch period?</h3>
                    <p class="text-slate-600">We'll introduce competitive pricing and give existing users exclusive loyalty benefits and discounts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
