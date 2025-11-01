# Conservative unused file scanner
# Scans files under resources/ and app/ for occurrences of their basenames across the repo.
# Excludes vendor, node_modules, public/build, storage paths.

$extensions = @('.php', '.js', '.css', '.scss', '.blade.php')
$targets = Get-ChildItem -Path resources, app -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $extensions -contains $_.Extension }

$all = Get-ChildItem -Path . -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
    $p = $_.FullName.ToLower()
    -not ($p -like '*\\node_modules\\*' -or $p -like '*\\vendor\\*' -or $p -like '*\\public\\build\\*' -or $p -like '*\\storage\\*')
}

$out = @()
foreach ($t in $targets) {
    $name = $t.Name
    try {
        $count = (Select-String -Path ($all | Select-Object -ExpandProperty FullName) -Pattern ([regex]::Escape($name)) -SimpleMatch -ErrorAction SilentlyContinue).Count
    } catch {
        $count = 0
    }
    if ($count -le 1) {
        $out += [PSCustomObject]@{ Name = $name; Count = $count; Path = $t.FullName }
    }
}

if ($out.Count -eq 0) {
    Write-Output "No conservative unused-file candidates found."
} else {
    Write-Output "Conservative unused-file candidates (basename, occurrences in repo, full path):"
    $out | Sort-Object Count, Name | Format-Table -AutoSize
}
