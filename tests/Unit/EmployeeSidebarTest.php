<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmployeeSidebarTest extends TestCase
{
    public function test_employees_menu_is_separated_from_dropdown(): void
    {
        $sidebarPath = dirname(__DIR__, 2) . '/resources/views/backend/layouts/sidebar.blade.php';
        $this->assertFileExists($sidebarPath);
        $sidebarContent = file_get_contents($sidebarPath);

        // 1. Standalone employee link exists with permission check and icon
        $this->assertStringContainsString("@can('read', \App\Models\Employee::class)", $sidebarContent);
        $this->assertStringContainsString("route('admin.employees.index')", $sidebarContent);
        $this->assertStringContainsString('ri-team-line', $sidebarContent);

        // 2. TAG Masters collapse dropdown should NOT contain the employees item
        $parts = explode('id="sidebarTagMasters"', $sidebarContent);
        $this->assertCount(2, $parts, 'sidebarTagMasters dropdown section must be found');
        $tagMastersContent = explode('</ul>', $parts[1])[0];
        $this->assertStringNotContainsString('admin.employees.index', $tagMastersContent, 'Employees menu item should not be inside TAG Masters dropdown');

        // 3. TAG Masters container permission should not include Employee::class
        $tagMastersCheck = explode('<li class="menu-title"><i class="ri-more-fill"></i> <span>TAG Masters</span></li>', $sidebarContent)[0];
        $lastIf = substr($tagMastersCheck, strrpos($tagMastersCheck, '@if'));
        $this->assertStringNotContainsString('Employee::class', $lastIf, 'TAG Masters parent @if should not check Employee::class');

        // 4. TAG Masters aria-expanded and show should exclude admin/masters/employees*
        $this->assertStringContainsString("!request()->is('admin/masters/employees*')", $sidebarContent);
    }
}

