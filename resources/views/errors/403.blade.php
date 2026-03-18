<h1 class="fs-24 fw-7 text-danger mb-3">Truy cập bị từ chối</h1>

<p class="fs-16 text-gray3 mb-4">
    {{ $exception->getMessage() ?: "Bạn không có quyền truy cập vào trang này. Vui lòng liên hệ quản trị viên nếu bạn cho rằng đây là lỗi."}}
</p>
