<?php
declare(strict_types=1);

require_once __DIR__ . '/../../user/apps/api/common.php';

apps_ensure_tables();
$adminId = apps_require_admin();
$db = apps_db();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
	$stmt = $db->query("
		SELECT a.id, a.slug, a.name, a.description, a.icon_path, a.entry_file,
			   a.is_published, a.created_at, COUNT(i.user_id) AS installs
		FROM vphone_apps a
		LEFT JOIN vphone_app_installs i ON i.app_id = a.id
		GROUP BY a.id
		ORDER BY a.created_at DESC
	");
	apps_json([
		'success' => true,
		'apps' => $stmt->fetchAll(PDO::FETCH_ASSOC),
		'limits' => [
			'max_files' => min(500, max(0, (int)ini_get('max_file_uploads') - 1)),
			'max_file_bytes' => min(20 * 1024 * 1024, apps_size_bytes((string)ini_get('upload_max_filesize'))),
			'post_max_size' => (string)ini_get('post_max_size'),
		],
	]);
}

if ($method === 'PATCH') {
	$body = json_decode((string)file_get_contents('php://input'), true);
	$appId = filter_var($body['id'] ?? null, FILTER_VALIDATE_INT);
	if (!$appId || !isset($body['is_published'])) {
		apps_json(['success' => false, 'message' => 'App and publish status are required.'], 400);
	}
	$stmt = $db->prepare('UPDATE vphone_apps SET is_published = ? WHERE id = ?');
	$stmt->execute([(int)(bool)$body['is_published'], $appId]);
	if ($stmt->rowCount() === 0) {
		$check = $db->prepare('SELECT id FROM vphone_apps WHERE id = ?');
		$check->execute([$appId]);
		if (!$check->fetchColumn()) {
			apps_json(['success' => false, 'message' => 'App not found.'], 404);
		}
	}
	apps_json(['success' => true]);
}

if ($method !== 'POST') {
	header('Allow: GET, POST, PATCH');
	apps_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMaxBytes = apps_size_bytes((string)ini_get('post_max_size'));
if ($contentLength > 0 && $postMaxBytes > 0 && $contentLength > $postMaxBytes) {
	apps_json(['success' => false, 'message' => 'The selected app folder exceeds PHP post_max_size. Increase post_max_size in php.ini and restart Apache.'], 413);
}

$name = trim((string)($_POST['name'] ?? ''));
$description = trim((string)($_POST['description'] ?? ''));
$folderName = trim((string)($_POST['folder_name'] ?? ''));
$slug = strtolower($folderName);
$slug = preg_replace('/[^a-z0-9_-]+/', '-', $slug) ?? '';
$slug = trim($slug, '-_');

if ($name === '' || strlen($name) > 100 || $slug === '' || strlen($slug) > 80 || strlen($description) > 500) {
	apps_json(['success' => false, 'message' => 'Enter a valid app name, folder name, and description (maximum 500 characters).'], 400);
}

$existing = $db->prepare('SELECT id FROM vphone_apps WHERE slug = ? LIMIT 1');
$existing->execute([$slug]);
if ($existing->fetchColumn()) {
	apps_json(['success' => false, 'message' => 'That app folder name is already in use.'], 409);
}

$icon = $_FILES['icon'] ?? null;
if (!is_array($icon) || ($icon['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || (int)$icon['size'] > 2 * 1024 * 1024) {
	apps_json(['success' => false, 'message' => 'Choose an app icon image smaller than 2 MB.'], 400);
}
$iconInfo = @getimagesize($icon['tmp_name']);
$iconMime = $iconInfo['mime'] ?? '';
$iconExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!isset($iconExtensions[$iconMime])) {
	apps_json(['success' => false, 'message' => 'App icons must be a JPEG, PNG, GIF, or WebP image.'], 400);
}

$files = $_FILES['project_files'] ?? null;
if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
	apps_json(['success' => false, 'message' => 'Choose the app project folder to upload.'], 400);
}

$uploads = [];
$totalBytes = 0;
$entryFile = '';
$fileCount = count($files['name']);
$maxFileUploads = min(500, max(0, (int)ini_get('max_file_uploads') - 1));
$expectedFileCount = filter_var($_POST['expected_file_count'] ?? null, FILTER_VALIDATE_INT);
if ($fileCount < 1 || $maxFileUploads < 1 || $fileCount > $maxFileUploads ||
	($expectedFileCount && $expectedFileCount !== $fileCount)) {
	apps_json([
		'success' => false,
		'message' => 'This PHP setup accepts up to ' . $maxFileUploads . ' project files per upload. Increase max_file_uploads in php.ini to upload larger app folders.',
	], 400);
}

foreach ($files['name'] as $index => $rawName) {
	$error = (int)($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
	if ($error !== UPLOAD_ERR_OK) {
		apps_json(['success' => false, 'message' => 'One of the app project files did not upload. Check PHP upload limits and try again.'], 400);
	}
	$relative = str_replace('\\', '/', (string)$rawName);
	$parts = explode('/', $relative);
	if (count($parts) > 1) {
		array_shift($parts);
	}
	if (!$parts || count($parts) > 12) {
		apps_json(['success' => false, 'message' => 'App files can be nested no more than 12 folders deep.'], 400);
	}
	foreach ($parts as $part) {
		if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.') ||
			!preg_match('/^[A-Za-z0-9 _().@+-]{1,120}$/', $part)) {
			apps_json(['success' => false, 'message' => 'The project folder contains an unsupported file or path.'], 400);
		}
	}
	if (in_array(strtolower(end($parts)), ['.htaccess', 'web.config'], true)) {
		apps_json(['success' => false, 'message' => 'Server configuration files cannot be uploaded as an app.'], 400);
	}

	$size = (int)($files['size'][$index] ?? 0);
	$maxFileBytes = min(20 * 1024 * 1024, apps_size_bytes((string)ini_get('upload_max_filesize')));
	if ($size > $maxFileBytes) {
		apps_json(['success' => false, 'message' => 'Each project file must fit within this PHP setup’s upload_max_filesize limit.'], 400);
	}
	$totalBytes += $size;
	if ($totalBytes > 100 * 1024 * 1024) {
		apps_json(['success' => false, 'message' => 'The app project folder must be 100 MB or smaller.'], 400);
	}

	$path = implode('/', $parts);
	if (count($parts) === 1 && in_array(strtolower($parts[0]), ['index.php', 'index.html', 'index.htm'], true)) {
		if ($entryFile === '' || strtolower($parts[0]) === 'index.php') {
			$entryFile = $path;
		}
	}
	$uploads[] = ['tmp_name' => $files['tmp_name'][$index], 'relative' => $path];
}

if ($entryFile === '') {
	apps_json(['success' => false, 'message' => 'The app folder must contain an index.php, index.html, or index.htm in its root.'], 400);
}

$appsRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'apps';
$publishRoot = $appsRoot . DIRECTORY_SEPARATOR . 'published';
$iconRoot = $appsRoot . DIRECTORY_SEPARATOR . 'icons';
if ((!is_dir($publishRoot) && !mkdir($publishRoot, 0755, true)) ||
	(!is_dir($iconRoot) && !mkdir($iconRoot, 0755, true))) {
	apps_json(['success' => false, 'message' => 'Could not create the app storage folders. Check folder permissions.'], 500);
}

$stageName = '.staging-' . bin2hex(random_bytes(12));
$stageRoot = $publishRoot . DIRECTORY_SEPARATOR . $stageName;
$appRoot = $publishRoot . DIRECTORY_SEPARATOR . $slug;
$iconName = $slug . '-' . bin2hex(random_bytes(6)) . '.' . $iconExtensions[$iconMime];
$iconPath = $iconRoot . DIRECTORY_SEPARATOR . $iconName;

if (!mkdir($stageRoot, 0755, true)) {
	apps_json(['success' => false, 'message' => 'Could not prepare app storage. Check folder permissions.'], 500);
}

$placedIcon = false;
$publishedFolderCreated = false;
try {
	foreach ($uploads as $file) {
		$destination = $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['relative']);
		$destinationDir = dirname($destination);
		if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true)) {
			throw new RuntimeException('Could not create an app subfolder.');
		}
		if (!move_uploaded_file($file['tmp_name'], $destination)) {
			throw new RuntimeException('Could not save an uploaded app file.');
		}
	}
	if (!move_uploaded_file($icon['tmp_name'], $iconPath)) {
		throw new RuntimeException('Could not save the app icon.');
	}
	$placedIcon = true;
	if (file_exists($appRoot) || !rename($stageRoot, $appRoot)) {
		throw new RuntimeException('An app with this folder name already exists or could not be published.');
	}
	$publishedFolderCreated = true;

	$stmt = $db->prepare('INSERT INTO vphone_apps (slug, name, description, icon_path, entry_file, created_by) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt->execute([$slug, $name, $description, '/virtual-phone/user/apps/icons/' . $iconName, $entryFile, $adminId]);
	apps_json(['success' => true, 'message' => $name . ' was added to the Play Store.']);
} catch (Throwable $error) {
	if (is_dir($stageRoot)) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($stageRoot, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $item) {
			$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
		}
		rmdir($stageRoot);
	}
	if ($publishedFolderCreated && is_dir($appRoot)) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($appRoot, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $item) {
			$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
		}
		rmdir($appRoot);
	}
	if ($placedIcon && is_file($iconPath)) {
		unlink($iconPath);
	}
	error_log('Virtual Phone app upload failed: ' . $error->getMessage());
	apps_json(['success' => false, 'message' => 'Could not publish that app folder. Check the files and folder permissions, then try again.'], 500);
}