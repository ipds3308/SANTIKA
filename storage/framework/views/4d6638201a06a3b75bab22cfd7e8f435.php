<?php $__env->startSection('title', 'Login Admin - SANTIKA BPS'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width: 400px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">Login Admin</h2>

        <?php if(session('error')): ?>
        <div style="background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-size: 14px; border: 1px solid #ffeeba;">
            <i class="fa fa-exclamation-triangle me-1"></i> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <form id="formLogin" action="<?php echo e(url('/login')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <button type="submit" id="btnLoginSubmit" style="width: 100%; padding: 12px; background: #2c3e50; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold;">
            Masuk
        </button>
    </form>

    <script>
        document.getElementById('formLogin').addEventListener('submit', function (event) {
            const btn = document.getElementById('btnLoginSubmit');

            // Jika tombol sudah dalam kondisi diproses, batalkan submit kedua ini sepenuhnya
            if (btn.disabled) {
                event.preventDefault();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';
        });
    </script>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views\pages\login.blade.php ENDPATH**/ ?>