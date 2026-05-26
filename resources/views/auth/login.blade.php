<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ESS - Login</title>
    @php
        $loginBanner = is_file(public_path('images/deped-marikina-login.jpg'))
            ? '/images/deped-marikina-login.jpg'
            : '/images/deped-marikina-login.png';
    @endphp
    <link rel="preload" href="{{ $loginBanner }}" as="image">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <main class="login">
        <section class="welcome">
            <img
                src="{{ $loginBanner }}"
                alt="DepEd Schools Division Office - Marikina City NCR"
                class="welcome-banner"
                width="1400"
                height="788"
                decoding="async"
                fetchpriority="high"
            >
        </section>

        <section class="panel">
            <form class="form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <h3>Sign In</h3>

                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif

                <label for="email">DepEd Email</label>
                <div class="field">
                    <span class="icon">@</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@deped.gov.ph" required autofocus autocomplete="username">
                </div>

                <label for="password">Password</label>
                <div class="field">
                    <span class="icon">#</span>
                    <input id="password" name="password" type="password" placeholder="Password" required autocomplete="current-password">
                    <button class="eye" type="button" aria-label="Show password" data-toggle-password>Show</button>
                </div>

                <button class="submit" type="submit">Sign In</button>
                <a class="forgot" href="#">Forgot your password?</a>

                <div class="logos">
                    <div class="deped">Dep<span>ED</span></div>
                    <div class="bagong"><div class="ribbon"></div><div>BAGONG<br>PILIPINAS</div></div>
                </div>
                <div class="footer">Developed by SDO - Marikina ICTU</div>
                <div class="demo">Demo account: admin@deped.gov.ph / password</div>
            </form>
        </section>
    </main>
    <script>
        document.querySelector('[data-toggle-password]')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            this.textContent = hidden ? 'Hide' : 'Show';
        });
    </script>
</body>
</html>
