<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ESS - Login</title>
    <style>
        :root {
            --blue: #1f5fbd;
            --blue-dark: #174ca2;
            --gray: #5f6875;
            --line: #d9dde4;
            --red: #d8242f;
            --gold: #f4b51b;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Arial, Helvetica, sans-serif; color: #202b3b; background: #fff; }
        .login { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; }
        .welcome {
            background: var(--blue); color: #fff; display: grid; place-items: center; padding: 48px;
            text-align: center;
        }
        .welcome-inner { max-width: 740px; }
        .seal {
            width: 88px; height: 88px; margin: 0 auto 28px; border-radius: 50%;
            display: grid; place-items: center; background: #f7cf39; color: #102d63;
            border: 5px solid rgba(255,255,255,.75); font-size: 22px; font-weight: 900;
            box-shadow: 0 12px 28px rgba(0,0,0,.18);
        }
        .division { font-size: 27px; font-weight: 700; margin-bottom: 28px; }
        h1 { margin: 0 0 8px; font-size: 38px; letter-spacing: 0; }
        h2 { margin: 0 0 26px; font-size: 28px; letter-spacing: 0; }
        .copy { margin: 0 auto; max-width: 720px; color: rgba(255,255,255,.88); line-height: 1.85; font-size: 16px; }
        .panel { display: grid; place-items: center; padding: 42px; }
        .form { width: min(420px, 100%); }
        .form h3 { margin: 0 0 34px; font-size: 39px; color: #5d626b; letter-spacing: 0; }
        label { display: block; font-size: 15px; margin-bottom: 8px; }
        .field { position: relative; margin-bottom: 22px; }
        .field input {
            width: 100%; height: 49px; border: 1px solid var(--line); border-radius: 6px;
            padding: 0 44px; font-size: 20px; color: #596273; outline: none;
        }
        .field input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(31,95,189,.12); }
        .icon { position: absolute; left: 15px; bottom: 14px; color: #9ba2ad; font-size: 15px; }
        .eye { position: absolute; right: 15px; bottom: 14px; color: #9ba2ad; border: 0; background: transparent; cursor: pointer; }
        .submit {
            width: 100%; height: 39px; border: 0; border-radius: 5px; background: var(--blue-dark);
            color: #fff; font-weight: 700; cursor: pointer; margin-top: 6px;
        }
        .submit:hover { background: #123f87; }
        .forgot { display: block; text-align: center; color: var(--blue); margin: 18px 0 42px; font-size: 14px; }
        .logos { display: flex; align-items: center; justify-content: center; gap: 28px; margin-bottom: 30px; }
        .deped { font-weight: 900; color: var(--blue); font-size: 26px; }
        .deped span { color: var(--red); }
        .bagong { display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 800; color: #123f87; }
        .ribbon { width: 44px; height: 38px; border-radius: 50%; background: conic-gradient(from 220deg, var(--blue), var(--red), var(--gold), var(--blue)); }
        .footer { text-align: center; color: #8a9099; font-size: 14px; }
        .error { padding: 10px 12px; border-radius: 6px; background: #ffecef; color: #b11725; margin-bottom: 18px; font-size: 14px; }
        .demo { margin-top: 16px; color: #8a9099; font-size: 13px; text-align: center; line-height: 1.5; }

        @media (max-width: 900px) {
            .login { grid-template-columns: 1fr; }
            .welcome { min-height: 48vh; padding: 36px 22px; }
            .panel { padding: 34px 22px; }
            h1 { font-size: 31px; }
            h2 { font-size: 23px; }
            .division { font-size: 22px; }
            .form h3 { font-size: 33px; }
        }
    </style>
</head>
<body>
    <main class="login">
        <section class="welcome">
            <div class="welcome-inner">
                <div class="seal">SDO</div>
                <div class="division">Schools Division Office - Marikina City</div>
                <h1>Welcome to</h1>
                <h2>Elementary Teacher Audit System</h2>
                <p class="copy">
                    The Teacher Audit platform is your central hub for reviewing enrollment,
                    sections, teacher requirements, shortages, and school-level staffing data for
                    SY 2025-2026. It provides fast, secure, and organized access to audit summaries
                    without the hassle of manual workbook checking.
                </p>
            </div>
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
                    <input id="email" name="email" type="email" value="{{ old('email', 'admin@deped.gov.ph') }}" placeholder="name@deped.gov.ph" required autofocus>
                </div>

                <label for="password">Password</label>
                <div class="field">
                    <span class="icon">#</span>
                    <input id="password" name="password" type="password" placeholder="Password" required>
                    <button class="eye" type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password';">show</button>
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
</body>
</html>
