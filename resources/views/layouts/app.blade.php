<!doctype html>
<html lang="pt-BR">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Dashboard') · Aurora Airlines</title>
 @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<header class="topbar d-flex align-items-center px-3 px-lg-4 gap-3">
 @auth <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menu"><i aria-hidden="true" class="bi bi-list"></i></button> @endauth
 <a class="brand fs-5" href="{{ auth()->check() ? route('dashboard') : route('market.index') }}"><span class="brand-mark"><i aria-hidden="true" class="bi bi-airplane-fill"></i></span>Aurora <span class="fw-normal d-none d-sm-inline">Airlines</span></a>
 <div class="ms-auto d-flex align-items-center gap-3">
 @auth
  <span class="small d-none d-md-inline">{{ auth()->user()->name }} <span class="badge bg-light text-secondary ms-2">{{ ['admin'=>'Administrador','agent'=>'Atendente','customer'=>'Cliente'][auth()->user()->role] }}</span></span>
  <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary" type="submit"><i aria-hidden="true" class="bi bi-box-arrow-right"></i> Sair</button></form>
 @else
  <a class="text-secondary small d-none d-sm-inline" href="{{ route('market.index') }}">Voos</a><a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Entrar</a><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Criar conta</a>
 @endauth
 </div>
</header>
<div class="@auth app-shell @endauth">
 @auth
 <aside id="sidebar" class="sidebar collapse d-lg-block">
  <div class="sidebar-label">Sua viagem começa aqui</div>
  <nav class="nav flex-column" aria-label="Menu principal">
   <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i aria-hidden="true" class="bi bi-grid-1x2"></i>Dashboard</a>
   <a class="nav-link {{ request()->routeIs('market.*') ? 'active' : '' }}" href="{{ route('market.index') }}"><i aria-hidden="true" class="bi bi-search"></i>Comprar passagens</a>
   <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}"><i aria-hidden="true" class="bi bi-ticket-perforated"></i>{{ auth()->user()->isStaff() ? 'Passagens e reservas' : 'Minhas passagens' }}</a>
   @if(auth()->user()->customer)<a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i aria-hidden="true" class="bi bi-person-circle"></i>Meu perfil de passageiro</a>@endif
   @can('manage-airline')
   <div class="sidebar-label">Operação aérea</div>
   @foreach(['airlines'=>['Companhias','building'],'airports'=>['Aeroportos','geo-alt'],'aircraft'=>['Aeronaves','airplane'],'flights'=>['Voos','calendar3'],'customers'=>['Passageiros','people']] as $resource=>[$label,$icon])
    <a class="nav-link {{ request()->routeIs($resource.'.*') ? 'active' : '' }}" href="{{ route($resource.'.index') }}"><i aria-hidden="true" class="bi bi-{{ $icon }}"></i>{{ $label }}</a>
   @endforeach
   <div class="sidebar-label">Comercial</div>
   <a class="nav-link" href="{{ route('tickets.create') }}"><i aria-hidden="true" class="bi bi-plus-circle"></i>Vender passagem</a>
   @endcan
   @can('view-sales')<a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}"><i aria-hidden="true" class="bi bi-bar-chart"></i>Relatório de vendas</a>@endcan
   @can('manage-users')<div class="sidebar-label">Administração</div><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i aria-hidden="true" class="bi bi-shield-lock"></i>Usuários e permissões</a>@endcan
  </nav>
 </aside>
 @endauth
 <main class="main-content @guest container @endguest">
  @if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="status">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button></div>@endif
  @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Confira os dados informados.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @yield('content')
  <footer class="footer no-print">Aurora Airlines · Gestão aérea e viagens · Horários em {{ config('app.timezone') }}</footer>
 </main>
</div>
</body>
</html>
