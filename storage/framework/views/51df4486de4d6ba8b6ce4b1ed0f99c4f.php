<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'School Audit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'School Audit']); ?>
    <div class="topbar">
        <div>
            <h1>School Audit</h1>
            <p>Review grade-level enrollment, sections, class size, and teacher shortage.</p>
        </div>
    </div>

    <form class="filters" method="GET" action="<?php echo e(route('schools')); ?>">
        <select name="school" aria-label="School">
            <?php $__currentLoopData = $schoolOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($school['code']); ?>" <?php if($selectedSchool === $school['code']): echo 'selected'; endif; ?>>
                    <?php echo e($school['name']); ?> (<?php echo e($school['code']); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="button" type="submit">View School</button>
    </form>

    <?php if(session('status')): ?>
        <div class="notice"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="notice error"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <section class="summary-strip">
        <div class="mini-stat">
            <span>Total Enrolled</span>
            <strong><?php echo e(number_format($summary->learners ?? 0)); ?></strong>
        </div>
        <div class="mini-stat">
            <span>Sections</span>
            <strong><?php echo e(number_format($summary->sections ?? 0)); ?></strong>
        </div>
        <div class="mini-stat">
            <span>Required Teachers</span>
            <strong><?php echo e(number_format($summary->required_teachers ?? 0)); ?></strong>
        </div>
        <div class="mini-stat">
            <span>Available Teachers</span>
            <strong><?php echo e(number_format($summary->available_teachers ?? 0)); ?></strong>
        </div>
        <div class="mini-stat">
            <span>Need Teachers</span>
            <strong><?php echo e(number_format($summary->shortage ?? 0)); ?></strong>
        </div>
    </section>

    <div class="card">
        <div class="card-title" style="padding:18px 18px 0">
            <h2><?php echo e($selectedSchoolName); ?> <span class="muted">(<?php echo e($selectedSchool); ?>)</span></h2>
            <span class="muted"><?php echo e($rows->count()); ?> grade levels - source fields editable</span>
        </div>
        <form method="POST" action="<?php echo e(route('schools.update', $selectedSchool)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th class="num">Enrolled</th>
                            <th class="num">Sections</th>
                            <th class="num">Class Size</th>
                            <th class="num">Teacher Requirement</th>
                            <th class="num">Current Teachers</th>
                            <th class="num">Surplus</th>
                            <th class="num">Need Teachers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong>Grade <?php echo e($row->grade_level); ?></strong></td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" name="rows[<?php echo e($row->id); ?>][learners]" value="<?php echo e(old("rows.$row->id.learners", $row->learners)); ?>">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="1" name="rows[<?php echo e($row->id); ?>][sections]" value="<?php echo e(old("rows.$row->id.sections", $row->sections)); ?>">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" step="0.01" name="rows[<?php echo e($row->id); ?>][class_size]" value="<?php echo e(old("rows.$row->id.class_size", number_format($row->class_size, 2, '.', ''))); ?>">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" name="rows[<?php echo e($row->id); ?>][required_teachers]" value="<?php echo e(old("rows.$row->id.required_teachers", $row->required_teachers)); ?>">
                                </td>
                                <td class="num">
                                    <input class="editable" type="number" min="0" name="rows[<?php echo e($row->id); ?>][available_teachers]" value="<?php echo e(old("rows.$row->id.available_teachers", $row->available_teachers)); ?>">
                                </td>
                                <td class="num"><span class="badge ok"><?php echo e(number_format($row->surplus)); ?></span></td>
                                <td class="num"><span class="badge <?php echo e($row->shortage > 0 ? 'danger' : ''); ?>"><?php echo e(number_format($row->shortage)); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8">No school audit records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:16px 18px 18px">
                <a class="button secondary" href="<?php echo e(route('schools', ['school' => $selectedSchool])); ?>">Cancel</a>
                <button class="button" type="submit">Save Changes</button>
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
<?php /**PATH C:\eicee desktop\thesis 2.0\resources\views/schools.blade.php ENDPATH**/ ?>