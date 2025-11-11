<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Restaurant Management')</title>
    <!-- Font Awesome (professional icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite('resources/css/app.css')
    <style>
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.5) transparent;
        }
        
        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        *::-webkit-scrollbar-track {
            background: transparent;
        }
        
        *::-webkit-scrollbar-thumb {
            background-color: rgba(59, 130, 246, 0.5);
            border-radius: 3px;
        }
        
        *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(59, 130, 246, 0.7);
        }

        @media (max-width: 768px) {
            body.sidebar-open {
                overflow: hidden;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-800 to-slate-900 text-white shadow-lg flex flex-col h-screen left-0 top-0 z-40 transform md:translate-x-0 -translate-x-full transition-transform duration-300">
            <!-- Logo / Brand -->
            <div class="p-6 border-b border-slate-700">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center text-xl">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold">RMS</h1>
                        <p class="text-xs text-slate-300">Restaurant Management</p>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto" aria-label="Main navigation">
                @php
                    $links = [
                        'dashboard' => ['icon' => 'fa-chart-simple', 'label' => 'Dashboard'],
                        'categories.index' => ['icon' => 'fa-folder', 'label' => 'Categories'],
                        'items.index' => ['icon' => 'fa-utensils', 'label' => 'Items'],
                        'sales.index' => ['icon' => 'fa-cash-register', 'label' => 'Sales'],
                        'payments.index' => ['icon' => 'fa-credit-card', 'label' => 'Payments'],
                        'expenses.index' => ['icon' => 'fa-file-invoice-dollar', 'label' => 'Expenses'],
                    ];
                @endphp

                @foreach ($links as $route => $data)
                @php $active = request()->routeIs($route); @endphp
                <a href="{{ route($route) }}" class="group flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-150 {{ $active ? 'bg-white text-blue-600 shadow-sm' : 'text-white hover:bg-blue-600/10 hover:text-white' }}">
                    <i class="fa-solid {{ $data['icon'] }} w-5"></i>
                    <span class="truncate">{{ $data['label'] }}</span>
                </a>
                @endforeach
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-700 text-center text-xs text-slate-300">
                <div>© {{ date('Y') }} RMS</div>
                <div class="mt-1">v1.0</div>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 md:hidden hidden z-30 transition-opacity duration-300" aria-hidden="true"></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-20">
                <div class="flex items-center justify-between px-4 md:px-6 py-3">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Menu Button -->
                        <button id="mobileMenuBtn" aria-label="Toggle menu" class="md:hidden text-slate-600 hover:text-slate-800 text-2xl">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <a href="{{ route('dashboard') }}" class="hidden md:inline-block">
                            <h2 class="text-lg font-semibold text-slate-800">@yield('page-title', 'Dashboard')</h2>
                        </a>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button id="userMenuBtn" aria-expanded="false" class="w-9 h-9 bg-slate-800 text-white rounded-full flex items-center justify-center hover:bg-slate-700 transition">
                                <i class="fa-solid fa-user"></i>
                            </button>

                            @auth
                            <div id="userMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow-lg hidden">
                                <div class="p-2 text-sm text-slate-700">
                                    <div class="py-1 font-semibold">{{ auth()->user()->name }}</div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left text-sm text-red-600 px-2 py-2 hover:bg-slate-50">Logout</button>
                                    </form>
                                </div>
                            </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 md:p-6">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');

        mobileMenuBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        });

        sidebarOverlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                }
            });
        });

        // User menu toggle
        userMenuBtn?.addEventListener('click', () => {
            if (userMenu) {
                userMenu.classList.toggle('hidden');
            }
        });

        // Close user menu when clicking outside
        document.addEventListener('click', (e) => {
            if (userMenu && !userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
