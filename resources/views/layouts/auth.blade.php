<!doctype html>
<html lang="id">

<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Kosync - @yield('title', 'Auth')</title>
		@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
		<main class="auth-page">
				<section class="auth-card">
						<a class="brand auth-brand" href="{{ route('login') }}">
								<span class="brand-mark"><img style="width: 100%" src="{{ asset('kosync-logo.jpeg') }}" alt=""></span>
								<span><strong>KOSYNC</strong><small>Kost Management System</small></span>
						</a>
						@yield('content')
				</section>
		</main>
</body>

</html>
