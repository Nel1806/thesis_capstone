<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard']); ?>
    <div class="topbar">
        <div>
            <h1>Elementary Teacher Audit</h1>
            <p>SY <?php echo e($import->school_year ?? '2025-2026'); ?> staffing overview from the uploaded workbook.</p>
        </div>
        <div class="pill">
            <?php echo e($import ? 'Imported '.$import->imported_at : 'No workbook imported yet'); ?>

        </div>
    </div>

    <section class="grid stats">
        <div class="card pad stat">
            <div class="label">Schools</div>
            <div class="value"><?php echo e(number_format($totals->schools ?? 0)); ?></div>
            <div class="hint">Elementary campuses</div>
        </div>
        <div class="card pad stat">
            <div class="label">Learners</div>
            <div class="value"><?php echo e(number_format($totals->learners ?? 0)); ?></div>
            <div class="hint">Total enrollment</div>
        </div>
        <div class="card pad stat">
            <div class="label">Sections</div>
            <div class="value"><?php echo e(number_format($totals->sections ?? 0)); ?></div>
            <div class="hint">Across grade levels</div>
        </div>
        <div class="card pad stat">
            <div class="label">Teacher Gap</div>
            <div class="value"><?php echo e(number_format(($totals->shortage ?? 0) - ($totals->surplus ?? 0))); ?></div>
            <div class="hint">Shortage minus surplus</div>
        </div>
    </section>

    <section class="grid two">
        <div class="card">
            <div class="card-title" style="padding:18px 18px 0">
                <h2>School Summary</h2>
                <span class="muted"><?php echo e($schools->count()); ?> records</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>School</th>
                            <th class="num">Learners</th>
                            <th class="num">Sections</th>
                            <th class="num">Required</th>
                            <th class="num">Available</th>
                            <th class="num">Gap</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($gap = ($school->shortage ?? 0) - ($school->surplus ?? 0)); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($school->school_name); ?></strong>
                                    <div class="muted"><?php echo e($school->school_code); ?></div>
                                </td>
                                <td class="num"><?php echo e(number_format($school->learners)); ?></td>
                                <td class="num"><?php echo e(number_format($school->sections)); ?></td>
                                <td class="num"><?php echo e(number_format($school->required_teachers)); ?></td>
                                <td class="num"><?php echo e(number_format($school->available_teachers)); ?></td>
                                <td class="num"><span class="badge <?php echo e($gap > 0 ? 'danger' : 'ok'); ?>"><?php echo e(number_format($gap)); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6">No audit data yet. Run <strong>php artisan audit:import</strong>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card pad">
            <div class="card-title">
                <h2>Grade Level Load</h2>
                <span class="muted">Learners</span>
            </div>
            <?php ($maxLearners = max(1, (int) $gradeLevels->max('learners'))); ?>
            <div class="bar">
                <?php $__currentLoopData = $gradeLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bar-row">
                        <strong>Grade <?php echo e($grade->grade_level); ?></strong>
                        <div class="track"><div class="fill" style="width: <?php echo e(min(100, ($grade->learners / $maxLearners) * 100)); ?>%"></div></div>
                        <span class="num"><?php echo e(number_format($grade->learners)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
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
<?php /**PATH C:\eicee desktop\thesis 2.0\resources\views/dashboard.blade.php ENDPATH**/ ?>