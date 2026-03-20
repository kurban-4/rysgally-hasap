{{--
    SYNC SCRIPT: Runs during HTML parsing, BEFORE first paint.
    .desktop-app-layout opening tag is already in the DOM at this point,
    so querySelector works immediately — zero flash in Tauri.
--}}
<script>
(function() {
    if (document.querySelector('.desktop-app-layout')) {
        document.body.classList.add('desktop-app-mode');
    } else {
        document.body.classList.add('normal-page-layout');
    }
})();
</script>

<aside class="sidebar-wrapper" id="sidebar-wrapper">
    
    <div class="sidebar-brand">
        <a class="d-flex align-items-center gap-3 text-decoration-none" href="{{ route('welcome') }}">
            <div class="brand-icon">
                <i class="bi bi-heart-pulse-fill text-white fs-4"></i>
            </div>
            <div class="sidebar-brand-text">
                <h5 class="fw-bold mb-0 text-white lh-1" style="letter-spacing:-0.5px;">SirinNabat</h5>
                <small class="text-white-50 text-uppercase fw-semibold" style="font-size:0.55rem;letter-spacing:2px;">Premium Health</small>
            </div>
        </a>
    </div>

    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">
        <i class="bi bi-x-lg"></i>
    </button>

    <nav class="sidebar-nav">
        <ul class="nav flex-column gap-1">
            @php
            /*
             * ACTIVE STATE FIX:
             * 'sales.*'  matches sales.customers.* too — wrong.
             * Use explicit patterns that don't bleed into sub-namespaces.
             */
            $navLinks = [
                [
                    'route' => 'employees.index',
                    'label' => 'Employees',
                    'icon'  => 'people',
                    'active' => request()->routeIs('employees.*'),
                ],
                [
                    'route' => 'storage.index',
                    'label' => 'Storage',
                    'icon'  => 'box-seam',
                    'active' => request()->routeIs('storage.*') || request()->routeIs('medicine.*'),
                ],
                [
                    'route' => 'sales.index',
                    'label' => 'Sales',
                    'icon'  => 'receipt',
                    // ONLY match sales routes that are NOT under sales.customers
                    'active' => request()->routeIs('sales.index')
                              || request()->routeIs('sales.cart.*')
                              || request()->routeIs('sales.close')
                              || (request()->routeIs('sales.*')
                                  && !request()->routeIs('sales.customers.*')),
                ],
                [
                    'route' => 'wholesale.index',
                    'label' => 'Wholesale',
                    'icon'  => 'truck',
                    'active' => request()->routeIs('wholesale.index')
                              || (request()->routeIs('wholesale.*')
                                  && !request()->routeIs('wholesale_storage.*')),
                ],
                [
                    'route' => 'wholesale_storage.index',
                    'label' => 'Wholesale Storage',
                    'icon'  => 'shop',
                    'active' => request()->routeIs('wholesale_storage.*'),
                ],
                [
                    'route' => 'sales.customers.index',
                    'label' => 'Customers',
                    'icon'  => 'person-badge',
                    'active' => request()->routeIs('sales.customers.*'),
                ],
            ];
            @endphp

            @foreach($navLinks as $link)
            <li class="nav-item">
                <a class="sidebar-link {{ $link['active'] ? 'active' : '' }}"
                   href="{{ route($link['route']) }}"
                   onclick="closeSidebarOnMobile()">
                    <i class="bi bi-{{ $link['icon'] }} link-icon"></i>
                    <span class="link-label">{{ strtoupper($link['label']) }}</span>
                    <span class="active-bar"></span>
                </a>
            </li>
            @endforeach
        </ul>
    </nav>

    <div class="sidebar-footer">
        {{-- Language --}}
        <div class="dropup mb-2">
            <button class="footer-btn w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-globe2" style="opacity:0.6;"></i>
                    <span class="link-label small fw-semibold">
                        @if(app()->getLocale() == 'en') English
                        @elseif(app()->getLocale() == 'tm') Türkmençe
                        @else Русский @endif
                    </span>
                </div>
                <i class="bi bi-chevron-up link-label" style="font-size:0.65rem;opacity:0.5;"></i>
            </button>
            <ul class="dropdown-menu lang-dropdown border-0 shadow-lg p-2 mb-2">
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='en' ? 'active-lang':'' }}"
                       href="{{ route('lang.switch',['locale'=>'en']) }}">
                    <span>🇺🇸</span><span class="small fw-medium">English</span>
                    @if(app()->getLocale()=='en')<i class="bi bi-check2 ms-auto" style="color:var(--teal);"></i>@endif
                </a></li>
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='tm' ? 'active-lang':'' }}"
                       href="{{ route('lang.switch',['locale'=>'tm']) }}">
                    <span>🇹🇲</span><span class="small fw-medium">Türkmençe</span>
                    @if(app()->getLocale()=='tm')<i class="bi bi-check2 ms-auto" style="color:var(--teal);"></i>@endif
                </a></li>
                <li><a class="dropdown-item lang-item rounded-3 {{ app()->getLocale()=='ru' ? 'active-lang':'' }}"
                       href="{{ route('lang.switch',['locale'=>'ru']) }}">
                    <span>🇷🇺</span><span class="small fw-medium">Русский</span>
                    @if(app()->getLocale()=='ru')<i class="bi bi-check2 ms-auto" style="color:var(--teal);"></i>@endif
                </a></li>
            </ul>
        </div>

        {{-- User --}}
        <div class="dropup">
            <div class="footer-btn user-btn" data-bs-toggle="dropdown" style="cursor:pointer;">
                <div class="user-avatar"><i class="bi bi-person-fill text-white" style="font-size:0.85rem;"></i></div>
                <span class="link-label small fw-semibold text-white text-truncate" style="max-width:130px;">Admin</span>
            </div>
            <ul class="dropdown-menu border-0 shadow-lg p-2 mb-2" style="border-radius:16px;min-width:200px;">
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3 text-danger fw-bold">
                            <i class="bi bi-power"></i> SIGN OUT
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</aside>

<button class="sidebar-hamburger" id="sidebarHamburger" aria-label="Open menu">
    <i class="bi bi-list"></i>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<style>
/* ── Variables ──────────────────────────────── */
:root {
    --sidebar-w: 250px;
    --sidebar-collapsed-w: 68px;
    --teal: #107A84;
    --teal-dark: #0c5e66;
}

/* ── Hard reset ─────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

html {
    height: 100%;
    margin: 0; padding: 0;
    overflow: hidden;
}

body {
    height: 100%;
    margin: 0; padding: 0;
    font-family: 'Inter', sans-serif;
    background: #f4f7f7;
    overflow: hidden;
}

/* ══════════════════════════════════════════════
   LAYOUT MODES — set synchronously, no flash
══════════════════════════════════════════════ */

/* — Normal pages (sidebar fixed, body has left padding) — */
body.normal-page-layout {
    overflow: auto;
    padding-left: var(--sidebar-w);
}
body.normal-page-layout .sidebar-wrapper {
    position: fixed;
    top: 0; bottom: 0; left: 0;
    width: var(--sidebar-w);
}

/* — Desktop app pages — */
body.desktop-app-mode {
    padding-left: 0;
    overflow: hidden;
}

/*
 * TAURI HEIGHT FIX:
 * Use position:fixed + inset:0 — guaranteed to fill exactly the usable window area.
 */
.desktop-app-layout {
    position: fixed;
    inset: 0;
    display: flex;
    flex-direction: row;
    overflow: hidden;
}

/* Sidebar inside desktop-app is a normal flex child, not fixed */
.desktop-app-layout .sidebar-wrapper {
    position: relative !important;
    flex-shrink: 0;
    height: 100%;
}

/* Main area fills remaining space */
.desktop-app-layout .app-main {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}

/* ══════════════════════════════════════════════
   FIX 1: Hide hamburger in desktop-app-mode
   (sidebar is always visible as a flex child)
══════════════════════════════════════════════ */
body.desktop-app-mode .sidebar-hamburger {
    display: none !important;
}

/* ══════════════════════════════════════════════
   FIX 2: Hide backdrop entirely in desktop-app-mode
   (no drawer needed, prevents the cloudy overlay)
══════════════════════════════════════════════ */
body.desktop-app-mode .sidebar-backdrop {
    display: none !important;
}

/* ══════════════════════════════════════════════
   SIDEBAR STYLES
══════════════════════════════════════════════ */
.sidebar-wrapper {
    width: var(--sidebar-w);
    background: linear-gradient(175deg, #107A84 0%, #0a5860 100%);
    display: flex;
    flex-direction: column;
    z-index: 1050;
    border-right: 1px solid rgba(255,255,255,0.07);
    overflow: hidden;
    transition: width 0.28s ease, transform 0.28s ease;
}

.sidebar-brand {
    padding: 20px 18px 14px;
    flex-shrink: 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.brand-icon {
    width: 42px; height: 42px;
    background: rgba(255,255,255,0.14);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.18);
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto; overflow-x: hidden;
    padding: 12px 8px;
    scrollbar-width: none;
}
.sidebar-nav::-webkit-scrollbar { display: none; }

.sidebar-link {
    position: relative;
    display: flex; align-items: center; gap: 10px;
    padding: 11px 13px;
    border-radius: 11px;
    color: rgba(255,255,255,0.58) !important;
    text-decoration: none;
    font-size: 0.77rem; font-weight: 600;
    white-space: nowrap; overflow: hidden;
    transition: background 0.14s, color 0.14s, transform 0.14s;
    margin-bottom: 1px;
}
.sidebar-link:hover {
    color: #fff !important;
    background: rgba(255,255,255,0.09);
    transform: translateX(3px);
}
.sidebar-link.active {
    color: #fff !important;
    background: rgba(255,255,255,0.15);
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
}
.link-icon { font-size: 1rem; flex-shrink: 0; }

.active-bar {
    position: absolute; left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 0;
    background: #fff; border-radius: 0 3px 3px 0;
    transition: height 0.22s ease;
}
.sidebar-link.active .active-bar { height: 20px; }

/* Footer */
.sidebar-footer {
    padding: 10px 8px;
    border-top: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
}
.footer-btn {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 9px 11px; border-radius: 11px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.72); font-size: 0.78rem;
    cursor: pointer; transition: background 0.14s;
}
.footer-btn:hover { background: rgba(255,255,255,0.13); }
.user-btn { justify-content: flex-start; }
.user-avatar {
    width: 28px; height: 28px;
    background: rgba(255,255,255,0.18); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.lang-dropdown { border-radius: 16px; }
.lang-item { display: flex; align-items: center; gap: 10px; padding: 7px 10px !important; }
.active-lang { background: rgba(16,122,132,0.08) !important; }

/* Mobile close btn */
.sidebar-close-btn {
    display: none;
    position: absolute; top: 13px; right: 13px;
    background: rgba(255,255,255,0.12); border: none; color: white;
    width: 32px; height: 32px; border-radius: 9px;
    align-items: center; justify-content: center;
    cursor: pointer; z-index: 10; font-size: 0.85rem;
}

/* Hamburger */
.sidebar-hamburger {
    display: none;
    position: fixed; top: 13px; left: 13px; z-index: 1200;
    background: var(--teal); border: none; color: white;
    width: 42px; height: 42px; border-radius: 12px; font-size: 1.35rem;
    align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16,122,132,0.42);
}

/* Backdrop
   NOTE: backdrop-filter removed — it causes a persistent compositing
   layer bug in Tauri's WebView that renders as a gray fog over the app. */
.sidebar-backdrop {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.46);
    z-index: 1100;
    
}
.sidebar-backdrop.active { display: block; animation: bdfade 0.2s ease; }
@keyframes bdfade { from { opacity: 0; } to { opacity: 1; } }

@media (max-width: 1023px) and (min-width: 768px) {
    .sidebar-wrapper { width: var(--sidebar-collapsed-w) !important; }
    body.normal-page-layout { padding-left: var(--sidebar-collapsed-w); }

    .sidebar-brand-text, .link-label { display: none !important; }

    .sidebar-link {
        justify-content: center;
        padding: 12px !important;
        transform: none !important;
    }
    .link-icon { font-size: 1.1rem; }

    .sidebar-link::after {
        content: attr(data-label);
        position: absolute; left: calc(var(--sidebar-collapsed-w) + 8px);
        background: #1a202c; color: #fff;
        padding: 5px 11px; border-radius: 8px;
        font-size: 0.72rem; white-space: nowrap;
        opacity: 0; pointer-events: none;
        transition: opacity 0.15s; z-index: 3000;
    }
    .sidebar-link:hover::after { opacity: 1; }

    .sidebar-footer { padding: 8px; }
    .footer-btn { justify-content: center; padding: 10px; }
    .footer-btn .link-label,
    .footer-btn .bi-chevron-up { display: none !important; }
}

/* ══════════════════════════════════════════════
   MOBILE <768: drawer
══════════════════════════════════════════════ */
@media (max-width: 767px) {
    body.normal-page-layout { padding-left: 0 !important; overflow: auto; }
    body.desktop-app-mode   { overflow: auto !important; }

    html, body { overflow: auto !important; }

    .desktop-app-layout {
        position: relative !important;
        inset: auto !important;
        flex-direction: column;
        min-height: 100vh; height: auto !important;
        overflow: auto !important;
    }

    .desktop-app-layout .sidebar-wrapper { position: fixed !important; }
    .desktop-app-layout .app-main { height: auto !important; overflow: auto !important; }

    /* Sidebar becomes drawer */
    .sidebar-wrapper {
        position: fixed !important;
        top: 0; bottom: 0; left: 0;
        width: 255px !important; height: 100% !important;
        transform: translateX(-100%); z-index: 1150;
    }
    .sidebar-wrapper.sidebar-open {
        transform: translateX(0);
        box-shadow: 6px 0 30px rgba(0,0,0,0.22);
    }

    /* Re-enable hamburger and close btn on mobile even in desktop-app-mode */
    body.desktop-app-mode .sidebar-hamburger { display: flex !important; }
    .sidebar-hamburger { display: flex !important; }
    .sidebar-close-btn  { display: flex !important; }
    .sidebar-brand-text, .link-label { display: block !important; opacity: 1 !important; }
    /* Hide hamburger while drawer is open */
.sidebar-wrapper.sidebar-open ~ .sidebar-hamburger {
    display: none !important;
}
}
</style>

<script>
(function() {
    window.openSidebar = function() {
        document.getElementById('sidebar-wrapper')?.classList.add('sidebar-open');
        document.getElementById('sidebarBackdrop')?.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    window.closeSidebar = function() {
        document.getElementById('sidebar-wrapper')?.classList.remove('sidebar-open');
        document.getElementById('sidebarBackdrop')?.classList.remove('active');
        document.body.style.overflow = '';
    };
    window.closeSidebarOnMobile = function() {
        if (window.innerWidth < 768) closeSidebar();
    };

    document.addEventListener('DOMContentLoaded', function() {
        // ── TOGGLE fix: open if closed, close if open ──
        document.getElementById('sidebarHamburger')?.addEventListener('click', function() {
            const wrapper = document.getElementById('sidebar-wrapper');
            if (wrapper?.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        document.getElementById('sidebarCloseBtn')?.addEventListener('click', closeSidebar);

        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            var lbl = link.querySelector('.link-label');
            if (lbl) link.setAttribute('data-label', lbl.textContent.trim());
        });
    });
})();
</script>