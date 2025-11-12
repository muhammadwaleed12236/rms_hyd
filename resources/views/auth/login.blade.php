<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In • Restaurant POS</title>

    <!-- Font Awesome (icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite('resources/css/app.css')

    <style>
        /* Scrollbar styling for POS terminals */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.5) transparent;
        }
        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        *::-webkit-scrollbar-thumb {
            background-color: rgba(59, 130, 246, 0.5);
            border-radius: 3px;
        }
        *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(59, 130, 246, 0.7);
        }

        /* POS feel: darker gradient background */
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
    </style>
</head>

<body class="text-slate-800 antialiased flex items-center justify-center min-h-screen">

    <!-- Outer container -->
    <div class="w-full max-w-md bg-white/95 backdrop-blur-sm shadow-2xl rounded-xl p-8 mx-4">

        <!-- Logo / Header -->
        <div class="flex flex-col items-center mb-6">
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-600 text-white mb-3">
                <i class="fa-solid fa-utensils text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">ABQ</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in to continue</p>
        </div>

        <!-- Error Message -->
        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ $errors->first() }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="email" 
                           name="email"
                           value="{{ old('email') }}"
                           required autofocus
                           class="w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-400"
                           placeholder="you@example.com">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="password" 
                           name="password"
                           required
                           class="w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-slate-400"
                           placeholder="••••••••">
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center text-slate-600">
                    <input type="checkbox" name="remember" class="mr-2 rounded text-blue-600 focus:ring-blue-500">
                    Remember me
                </label>
                {{-- <a href="#" class="text-blue-600 hover:underline">Forgot password?</a> --}}
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-150 flex items-center justify-center">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
            </button>
        </form>

        <!-- Footer -->
        <p class="mt-6 text-center text-xs text-slate-400">
            © {{ date('Y') }} ABQ. All rights reserved.
        </p>
    </div>

</body>
</html>
