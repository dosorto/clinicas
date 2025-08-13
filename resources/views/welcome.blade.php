<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediSystem - Sistema de Gestión Médica</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Opción 1: Logo PEQUEÑO (32px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-8 w-8 mr-2"> -->
                    
                    <!-- Opción 2: Logo MEDIANO (48px) - Recomendado para header -->
                    <img src="{{ asset('images/logo.jpg') }}" alt="Sanare Logo" class="h-12 w-12 mr-3">
                    
                    <!-- Opción 3: Logo GRANDE (64px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-16 w-16 mr-4"> -->
                    
                    <!-- Opción 4: Logo MUY GRANDE (80px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-20 w-20 mr-4"> -->
                    
                    <!-- Opción 5: Tamaño personalizado -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-14 w-auto mr-3"> -->
                    
                    <h1 class="text-2xl font-bold text-gray-800">Sanare</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('filament.admin.auth.login') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <!-- Logo grande en el centro -->
                <div class="mb-8 flex justify-center">
                    <!-- Opción 1: Logo MUY GRANDE (128px) -->
                    <img src="{{ asset('images/logo.jpg') }}" alt="Sanare Logo"  class="h-40 w-40 rounded-2xl border-4 border-blue-500 shadow-xl bg-white p-3">
                    
                    <!-- Opción 2: Logo GIGANTE (160px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-40 w-40"> -->
                    
                    <!-- Opción 3: Logo SÚPER GRANDE (192px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-48 w-48"> -->
                    
                    <!-- Opción 4: Logo con ancho automático -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-32 w-auto"> -->
                </div>
                <h1 class="text-5xl font-bold text-gray-900 mb-6">
                    Sistema de Gestión Médica
                    <span class="text-blue-600">Integral</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Plataforma completa para la administración de médicos, centros médicos, citas, consultas y recetas. 
                    Optimiza tu práctica médica con tecnología de vanguardia.
                </p>
                
                <!-- Features Icons -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                    <div class="text-center">
                        <i class="fas fa-user-md text-3xl text-blue-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Gestión de Médicos</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-hospital text-3xl text-green-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Centros Médicos</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-calendar-alt text-3xl text-purple-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Citas y Consultas</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-prescription-bottle-alt text-3xl text-red-600 mb-2"></i>
                        <p class="text-sm text-gray-700 font-medium">Recetas Médicas</p>
                    </div>
                </div>

                <a href="{{ route('filament.admin.auth.login') }}" 
                   class="inline-flex items-center bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 rounded-lg text-lg font-semibold shadow-lg hover:shadow-xl transition duration-300 transform hover:scale-105">
                    <i class="fas fa-stethoscope mr-3"></i>
                    Acceder al Sistema
                    <i class="fas fa-arrow-right ml-3"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Membership Plans Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Planes de Membresía</h2>
                <p class="text-xl text-gray-600">Elige el plan que mejor se adapte a las necesidades de tu práctica médica</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Plan Básico -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 card-hover overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <i class="fas fa-clinic-medical text-4xl text-blue-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Básico</h3>
                            <p class="text-gray-600 mt-2">Ideal para consultorios pequeños</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-blue-600">$29</span>
                            <span class="text-gray-600">/mes</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Hasta 100 pacientes</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Gestión de citas básica</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Historial médico digital</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Soporte por email</span>
                            </li>
                        </ul>

                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition duration-300">
                            Comenzar Ahora
                        </button>
                    </div>
                </div>

                <!-- Plan Profesional -->
                <div class="bg-white rounded-xl shadow-xl border-2 border-purple-500 card-hover overflow-hidden relative">
                    <div class="absolute top-0 left-0 right-0 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-center py-2 text-sm font-semibold">
                        MÁS POPULAR
                    </div>
                    <div class="p-8 pt-12">
                        <div class="text-center mb-6">
                            <i class="fas fa-hospital text-4xl text-purple-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Profesional</h3>
                            <p class="text-gray-600 mt-2">Para clínicas y centros médicos</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-purple-600">$79</span>
                            <span class="text-gray-600">/mes</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Pacientes ilimitados</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Gestión avanzada de citas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Múltiples médicos</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Reportes y analíticas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Soporte prioritario 24/7</span>
                            </li>
                        </ul>

                        <button class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 rounded-lg font-semibold transition duration-300 transform hover:scale-105">
                            Comenzar Ahora
                        </button>
                    </div>
                </div>

                <!-- Plan Empresarial -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 card-hover overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <i class="fas fa-building text-4xl text-yellow-500 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-900">Plan Empresarial</h3>
                            <p class="text-gray-600 mt-2">Para hospitales y redes médicas</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <span class="text-4xl font-bold text-yellow-600">$199</span>
                            <span class="text-gray-600">/mes</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Sin límites</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Múltiples sedes</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">API personalizada</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Integraciones avanzadas</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-gray-700">Gerente de cuenta dedicado</span>
                            </li>
                        </ul>

                        <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-3 rounded-lg font-semibold transition duration-300">
                            Contactar Ventas
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">¿Por qué elegir Sanare?</h2>
                <p class="text-xl text-blue-100">Tecnología avanzada al servicio de la medicina</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-shield-alt text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Seguridad Total</h3>
                    <p class="text-blue-100">Cumple con todas las normativas de protección de datos médicos</p>
                </div>
                
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-mobile-alt text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Acceso Móvil</h3>
                    <p class="text-blue-100">Disponible en cualquier dispositivo, en cualquier lugar</p>
                </div>
                
                <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-xl p-6 text-center">
                    <i class="fas fa-chart-line text-4xl text-white mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Reportes Inteligentes</h3>
                    <p class="text-blue-100">Analíticas avanzadas para optimizar tu práctica médica</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <!-- Opción 1: Logo PEQUEÑO (32px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-8 w-8 mr-2"> -->
                    
                    <!-- Opción 2: Logo MEDIANO (48px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-12 w-12 mr-3"> -->
                    
                    <!-- Opción 3: Logo GRANDE (64px) - Recomendado para footer -->
                    <img src="{{ asset('images/logo.jpg') }}" alt="Sanare Logo" class="h-16 w-16 rounded-2xl border-4 border-yellow-500 shadow-xl bg-white p-3">
                    
                    <!-- Opción 4: Logo MUY GRANDE (80px) -->
                    <!-- <img src="{{ asset('images/logo.png') }}" alt="MediSystem Logo" class="h-20 w-20 mr-4"> -->
                    
                    <h3 class="text-2xl font-bold">Sanare</h3>
                </div>
                <p class="text-gray-400 mb-6">Transformando la gestión médica con tecnología innovadora</p>
                <div class="flex justify-center space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-linkedin-in text-xl"></i>
                    </a>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800">
                    <p class="text-gray-500">© 2024 Sanare. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>