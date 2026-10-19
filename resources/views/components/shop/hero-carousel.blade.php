@props(['slides'])
@if($slides->isNotEmpty())
<section id="homeHero" class="mr-bulls-hero carousel slide" aria-roledescription="carrusel" aria-label="Colección Mr Bulls">
    <div class="carousel-inner">
        @foreach($slides as $slide)
        <div class="carousel-item {{ $loop->first ? 'active' : '' }}" role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $loop->count }}">
                        <div class="hero-backdrop">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($slide->image) }}" alt="{{ $slide->image_alt }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
            </div>
            <div class="hero-shade" aria-hidden="true"></div>
            <div class="container position-relative hero-foreground">
                <div class="row align-items-center min-vh-hero">
                    <div class="col-lg-8 col-xl-7 position-relative z-2">
                        <div class="hero-content">
                            @if($slide->eyebrow)<span class="hero-eyebrow">{{ $slide->eyebrow }}</span>@endif
                            @if($loop->first)
                                <h1 class="hero-title">{{ $slide->title }} @if($slide->highlight)<span>{{ $slide->highlight }}</span>@endif</h1>
                            @else
                                <h2 class="hero-title">{{ $slide->title }} @if($slide->highlight)<span>{{ $slide->highlight }}</span>@endif</h2>
                            @endif
                            @if($slide->description)<p class="hero-description">{{ $slide->description }}</p>@endif
                            <div class="d-flex flex-wrap gap-3 mt-4">
                                @if($slide->primary_label && \App\Models\HeroSlide::validLink($slide->primary_url) && $slide->primary_url)
                                <a href="{{ $slide->primary_url }}" class="btn hero-btn-primary btn-lg">{{ $slide->primary_label }} <i class="bx bx-right-arrow-alt ms-2" aria-hidden="true"></i></a>
                                @endif
                                @if($slide->secondary_label && \App\Models\HeroSlide::validLink($slide->secondary_url) && $slide->secondary_url)
                                <a href="{{ $slide->secondary_url }}" class="btn hero-btn-secondary btn-lg">{{ $slide->secondary_label }}</a>
                                @endif
                            </div>
                            @if($slide->brand_line)<div class="hero-brand-line mt-5"><span></span><small>{{ $slide->brand_line }}</small></div>@endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if($slides->count() > 1)
    <div class="container hero-carousel-controls d-flex align-items-center gap-3">
        <button class="btn hero-btn-secondary" type="button" data-bs-target="#homeHero" data-bs-slide="prev" aria-label="Diapositiva anterior">‹</button>
        <div class="carousel-indicators position-static m-0">
            @foreach($slides as $slide)
            <button type="button" data-bs-target="#homeHero" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" @if($loop->first) aria-current="true" @endif aria-label="Ver diapositiva {{ $loop->iteration }}"></button>
            @endforeach
        </div>
        <button class="btn hero-btn-secondary" type="button" data-bs-target="#homeHero" data-bs-slide="next" aria-label="Diapositiva siguiente">›</button>
        <button class="btn hero-btn-secondary" type="button" id="heroPause" aria-pressed="false">Pausar</button>
    </div>
    @endif


</section>
@if($slides->count() > 1)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('homeHero');
    if (!element || !window.bootstrap?.Carousel) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const carousel = window.bootstrap.Carousel.getOrCreateInstance(element, { interval: 6000, pause: false, touch: true, ride: false });
    const toggle = document.getElementById('heroPause');
    let paused = reducedMotion.matches;
    const update = () => {
        toggle.textContent = paused ? 'Reanudar' : 'Pausar';
        toggle.setAttribute('aria-pressed', String(paused));
        if (paused) carousel.pause(); else carousel.cycle();
    };
    toggle.addEventListener('click', () => { paused = !paused; update(); });
    element.addEventListener('mouseenter', () => carousel.pause());
    element.addEventListener('mouseleave', () => { if (!paused && !element.contains(document.activeElement)) carousel.cycle(); });
    element.addEventListener('focusin', () => carousel.pause());
    element.addEventListener('focusout', event => { if (!element.contains(event.relatedTarget) && !paused) carousel.cycle(); });
    reducedMotion.addEventListener('change', event => { if (event.matches) { paused = true; update(); } });
    update();
});
</script>
@endpush
@endif
@endif
@push('styles')
<style>
    #homeHero { background: #101820; color: #fff; isolation: isolate; }
    #homeHero .carousel-item { position: relative; transition: transform .6s ease-in-out; }
    #homeHero .hero-backdrop, #homeHero .hero-shade { position: absolute; inset: 0; pointer-events: none; }
    #homeHero .hero-backdrop { z-index: 0; }
    #homeHero .hero-backdrop img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
    #homeHero .hero-shade {
        z-index: 1;
        background: linear-gradient(90deg, rgba(5, 12, 20, .88) 0%, rgba(5, 12, 20, .73) 48%, rgba(5, 12, 20, .35) 100%),
                    linear-gradient(0deg, rgba(5, 12, 20, .65), transparent 45%);
    }
    #homeHero .hero-foreground { z-index: 2; }
    #homeHero .min-vh-hero { min-height: clamp(580px, 78vh, 820px); }
    #homeHero .hero-content { padding: 6rem 0 8rem; }
    #homeHero .hero-eyebrow { color: #fff; font-size: .75rem; letter-spacing: .28em; }
    #homeHero .hero-eyebrow::before { background: #ed6674; }
    #homeHero .hero-title { max-width: 760px; color: #fff; font-size: clamp(3.2rem, 6.5vw, 6.5rem); line-height: 1; text-wrap: balance; overflow-wrap: anywhere; text-shadow: 0 3px 24px rgba(0, 0, 0, .25); }
    #homeHero .hero-title span { color: var(--mb-red); }
    #homeHero .hero-description { max-width: 580px; color: #f0f2f5; font-size: clamp(1rem, 1.5vw, 1.15rem); line-height: 1.8; }
    #homeHero .hero-btn-primary { color: #142334; background: #fff; border-color: #fff; box-shadow: 0 8px 28px rgba(0, 0, 0, .16); }
    #homeHero .hero-btn-primary:hover { background: #f7dce0; border-color: #f7dce0; color: #142334; }
    #homeHero .hero-btn-secondary { color: #fff; background: rgba(10, 20, 30, .3); border-color: rgba(255, 255, 255, .65); backdrop-filter: blur(8px); }
    #homeHero .hero-btn-secondary:hover { color: #142334; background: #fff; border-color: #fff; }
    #homeHero .btn:focus-visible, #homeHero .carousel-indicators button:focus-visible { outline: 3px solid #ffb2ba; outline-offset: 5px; box-shadow: none; }
    #homeHero .hero-brand-line span { background: #ffb2ba; }
    #homeHero .hero-brand-line small { color: #e0e4e9; }
    #homeHero .hero-carousel-controls { position: absolute; z-index: 3; bottom: 1.75rem; left: 50%; transform: translateX(-50%); flex-wrap: wrap; }
    #homeHero .hero-carousel-controls .btn { padding: .5rem 1rem; border-radius: 999px; font-size: .85rem; }
    #homeHero .carousel-indicators { gap: .35rem; max-width: 45%; flex-wrap: wrap; }
    #homeHero .carousel-indicators button { width: 22px; height: 3px; margin: 4px 0; background-color: #fff; border: 0; border-radius: 999px; opacity: .45; }
    #homeHero .carousel-indicators button.active { opacity: 1; }
    @media (max-width: 767.98px) {
        #homeHero .min-vh-hero { min-height: 620px; }
        #homeHero .hero-content { padding: 4.5rem 0 8rem; }
        #homeHero .hero-shade { background: linear-gradient(90deg, rgba(5, 12, 20, .84), rgba(5, 12, 20, .64)), linear-gradient(0deg, rgba(5, 12, 20, .7), transparent); }
        #homeHero .hero-title { font-size: clamp(2.8rem, 11vw, 4.5rem); }
        #homeHero .hero-brand-line { display: none; }
        #homeHero .hero-carousel-controls { bottom: 1.25rem; gap: .6rem !important; }
    }
    @media (prefers-reduced-motion: reduce) { #homeHero .carousel-item { transition: none; } }
</style>
@endpush
