<!DOCTYPE html>
<html lang="en">

<head>

    @include('layout.header')

    <title>Login</title>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    {{-- Inline styles to make error messages red (can also be moved to auth.css) --}}
    <style>
        .error-message {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px 14px;
            border-radius: 6px;
            border-left: 4px solid #d32f2f;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .field-error {
            color: #d32f2f;
            font-size: 0.85rem;
            display: block;
            margin-top: 4px;
        }

        /* Optional: style the remember toggle to align nicely */
        .remember-group {
            margin: 12px 0 18px;
        }

        .remember-toggle {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            color: #333;
        }

        .remember-toggle input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1a73e8;
        }
    </style>

</head>

<body>

    <div class="main-container">

        <!-- LEFT SIDE -->
        <div class="left-container">
            <img src="{{ asset('images/ebook.jpg') }}" alt="ebook">
        </div>

        <!-- RIGHT SIDE -->
        <div class="right-container">

            <div class="login-box">

                <h2>Log In </h2>

                <p>
                    Enter your credentials to access your account
                </p>

                {{-- Display error message if any (now red) --}}
                @if (session('error'))
                    <div class="error-message">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Form with proper Laravel authentication --}}
                <form method="POST" action="{{ route('karate.login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" placeholder="Enter your username"
                            value="{{ old('username') }}" required autofocus>
                        @error('username')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Enter your password"
                            required>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="remember-group">
                        <label class="remember-toggle" for="remember">
                            <input id="remember" type="checkbox" name="remember" value="1"
                                {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="login-btn">
                        Log In
                    </button>

                </form>

            </div>

        </div>

    </div>

</body>

</html>
