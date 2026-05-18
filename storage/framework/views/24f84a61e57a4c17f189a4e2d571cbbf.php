<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Parameters']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Parameters']); ?>
    <div class="topbar">
        <div>
            <h1>Planning Parameters</h1>
            <p>Class organization and teacher requirement parameters based on the Excel workbook.</p>
        </div>
    </div>

    <?php if(session('status')): ?>
        <div class="notice"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="notice error"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="<?php echo e(route('parameters.update')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Level</th>
                            <th class="num">Minimum</th>
                            <th class="num">Maximum</th>
                            <th class="num">Rounded Half</th>
                            <th class="num">Small Excess</th>
                            <th class="num">Teacher Factor</th>
                            <th>Class Organization</th>
                            <th>Teacher Specialization</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($row->group_name); ?></td>
                                <td><strong><?php echo e($row->level); ?></strong></td>
                                <td class="num"><input class="editable" name="rows[<?php echo e($row->id); ?>][minimum]" value="<?php echo e(old("rows.$row->id.minimum", $row->minimum)); ?>"></td>
                                <td class="num"><input class="editable" name="rows[<?php echo e($row->id); ?>][maximum]" value="<?php echo e(old("rows.$row->id.maximum", $row->maximum)); ?>"></td>
                                <td class="num"><input class="editable" name="rows[<?php echo e($row->id); ?>][rounded_half]" value="<?php echo e(old("rows.$row->id.rounded_half", $row->rounded_half)); ?>"></td>
                                <td class="num"><input class="editable" name="rows[<?php echo e($row->id); ?>][small_excess]" value="<?php echo e(old("rows.$row->id.small_excess", $row->small_excess)); ?>"></td>
                                <td class="num"><input class="editable" type="number" step="0.01" min="0" name="rows[<?php echo e($row->id); ?>][teacher_factor]" value="<?php echo e(old("rows.$row->id.teacher_factor", $row->teacher_factor)); ?>"></td>
                                <td><?php echo e($row->class_organization); ?></td>
                                <td><?php echo e($row->teacher_specialization); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; padding:16px 18px 18px">
                <button class="button" type="submit">Save Parameters</button>
            </div>
        </form>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\eicee desktop\thesis 2.0\resources\views/parameters.blade.php ENDPATH**/ ?>