<x-layouts.app title="Parameters">
    <div class="topbar">
        <div>
            <h1>Planning Parameters</h1>
            <p>Class organization and teacher requirement parameters based on the Excel workbook.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice error">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('parameters.update') }}">
            @csrf
            @method('PUT')
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
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row->group_name }}</td>
                                <td><strong>{{ $row->level }}</strong></td>
                                <td class="num"><input class="editable" name="rows[{{ $row->id }}][minimum]" value="{{ old("rows.$row->id.minimum", $row->minimum) }}"></td>
                                <td class="num"><input class="editable" name="rows[{{ $row->id }}][maximum]" value="{{ old("rows.$row->id.maximum", $row->maximum) }}"></td>
                                <td class="num"><input class="editable" name="rows[{{ $row->id }}][rounded_half]" value="{{ old("rows.$row->id.rounded_half", $row->rounded_half) }}"></td>
                                <td class="num"><input class="editable" name="rows[{{ $row->id }}][small_excess]" value="{{ old("rows.$row->id.small_excess", $row->small_excess) }}"></td>
                                <td class="num"><input class="editable" type="number" step="0.01" min="0" name="rows[{{ $row->id }}][teacher_factor]" value="{{ old("rows.$row->id.teacher_factor", $row->teacher_factor) }}"></td>
                                <td>{{ $row->class_organization }}</td>
                                <td>{{ $row->teacher_specialization }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; padding:16px 18px 18px">
                <button class="button" type="submit">Save Parameters</button>
            </div>
        </form>
    </div>
</x-layouts.app>
