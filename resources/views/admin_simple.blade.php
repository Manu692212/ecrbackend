<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECR Wings Academy - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                        <h1 class="text-2xl font-bold">ECR Wings Academy</h1>
                    </div>
                    <div class="text-sm">
                        <span class="bg-blue-700 px-3 py-1 rounded-full">Admin Panel</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h2>
                
                <!-- API Info Card -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>API Base URL:</strong> <code class="bg-blue-100 px-2 py-1 rounded">{{ url('/api') }}</code>
                            </p>
                            <p class="text-sm text-blue-700 mt-1">
                                <strong>Default Admin:</strong> admin@ecrwings.com / admin123
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Admins</p>
                                <p class="text-2xl font-bold">2</p>
                            </div>
                            <i class="fas fa-users text-3xl text-blue-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Courses</p>
                                <p class="text-2xl font-bold">0</p>
                            </div>
                            <i class="fas fa-book text-3xl text-green-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Students</p>
                                <p class="text-2xl font-bold">0</p>
                            </div>
                            <i class="fas fa-user-graduate text-3xl text-purple-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100">Enrollments</p>
                                <p class="text-2xl font-bold">0</p>
                            </div>
                            <i class="fas fa-clipboard-list text-3xl text-orange-200"></i>
                        </div>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Available API Endpoints</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Authentication</h4>
                        <div class="space-y-1 text-sm">
                            <div><code class="bg-gray-200 px-2 py-1 rounded">POST /api/admins/login</code> - Admin login</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Admin Management</h4>
                        <div class="space-y-1 text-sm">
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/admins</code> - List admins</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">POST /api/admins</code> - Create admin</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/admins/{id}</code> - Get admin</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">PUT /api/admins/{id}</code> - Update admin</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">DELETE /api/admins/{id}</code> - Delete admin</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Course Management</h4>
                        <div class="space-y-1 text-sm">
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/courses</code> - List courses</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">POST /api/courses</code> - Create course</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/courses/{id}</code> - Get course</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">PUT /api/courses/{id}</code> - Update course</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">DELETE /api/courses/{id}</code> - Delete course</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Student Management</h4>
                        <div class="space-y-1 text-sm">
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/students</code> - List students</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">POST /api/students</code> - Create student</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/students/{id}</code> - Get student</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">PUT /api/students/{id}</code> - Update student</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">DELETE /api/students/{id}</code> - Delete student</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Enrollment Management</h4>
                        <div class="space-y-1 text-sm">
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/enrollments</code> - List enrollments</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">POST /api/enrollments</code> - Create enrollment</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">GET /api/enrollments/{id}</code> - Get enrollment</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">PUT /api/enrollments/{id}</code> - Update enrollment</div>
                            <div><code class="bg-gray-200 px-2 py-1 rounded">DELETE /api/enrollments/{id}</code> - Delete enrollment</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
