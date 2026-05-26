# Database structure

The Teacher Audit System stores data in two main categories:

## 1. Parameters (`audit_parameters`)

Class organization rules used to calculate sections, class size, and teacher requirements (maximum class size, rounded half, teacher factor, etc.).

## 2. School Year (`school_years` + `school_year_records`)

Each **school year** holds audit rows per school and grade.

### Supporting tables

| Table | Purpose |
|-------|---------|
| `school_years` | Catalog of SY labels (e.g. `2025-2026`) |
| `schools` | School name and code by basic education level |
| `audit_imports` | Import batch per year and level (elementary / secondary) |
| `school_grade_audits` | Legacy import rows (kept in sync with school year records) |

### School year record fields (`school_year_records`)

| Column | Description |
|--------|-------------|
| `school_name` | School Name |
| `basic_education_level` | Elementary or Secondary |
| `grade` | Grade label (e.g. Grade 1, Kindergarten) |
| `grade_level` | Numeric grade (0–6 elementary, 7–12 secondary) |
| `learners` | Enrolled learners (editable) |
| `sections` | Section count |
| `class_size` | Class Size |
| `teacher_requirement` | Teacher Requirements |
| `current_teachers` | Current Teachers |
| `teacher_surplus` | Teacher Surplus |
| `teacher_needs` | Teacher Needs |

After migrating, sync the catalog from config and existing imports:

```bash
php artisan migrate
php artisan audit:sync-catalog
```
