@php
    $userInfo = Auth::user();
    $displayFlag = false;
    if(in_array($userInfo->roles, config('constants.ADMIN_ROLES'))){
        $displayFlag = true;
    }
@endphp

<nav class="navbar navbar-expand-md mt-2" style="background-color:#1746A2">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="{{ route('home') }}"><i class="bi bi-house-fill"></i> Home</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#headerMenu" aria-controls="headerMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="headerMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link text-white"  role="button">Employees</a>
                    <ul class="dropdown-menu">
                        @if ($displayFlag)
                            <li><a href="#" class="dropdown-item small text-white">View Request</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a href="#" class="dropdown-item small text-white">View List</a></li>            
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link text-white"  role="button">Laptops</a>
                    <ul class="dropdown-menu">
                        @if ($displayFlag)
                            <li><a href="#" class="dropdown-item small text-white">View Request</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" class="dropdown-item small text-white">View Request Link</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a href="#" class="dropdown-item small text-white">View List</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="#" class="dropdown-item small text-white">Create Laptop</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link text-white"  role="button">Softwares</a>
                    <ul class="dropdown-menu">
                        @if ($displayFlag)
                            <li><a href="#" class="dropdown-item small text-white">View Request</a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endif
                        <li><a href="#" class="dropdown-item small text-white">View List</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="#" class="dropdown-item small text-white">Create Software</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link text-white"  role="button">Projects</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="dropdown-item small text-white">View Projects</a></li>
                        @if ($displayFlag)
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" class="dropdown-item small text-white">Create Project</a></li>
                        @endif
                    </ul>
                </li>
                @if ($displayFlag)
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link text-white"  role="button">Servers</a>
                        <ul class="dropdown-menu">
                            <li><a href="#" class="dropdown-item small text-white">View List</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" class="dropdown-item small text-white">Create Server</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
            <div class="d-flex text-lg-end">
                <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link text-white "  role="button">{{ $userInfo->last_name .', ' .$userInfo->first_name }}</a>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="dropdown-item small text-white">My Details</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="{{ route('logout') }}" class="dropdown-item small text-white">Logout</a></li>
                    </ul>
                </li>
            </ul>
            </div>

        </div>
    </div>
</nav>
