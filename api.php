<?php
/**
 * 广告管理API
 * 提供广告和位置的增删改查接口
 * 独立版本
 */

session_start();

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config_default.php';
}

// 广告位置code常量（与ad_positions表code字段对应）
define('AD_POSITION_HOME_BANNER', 'home_banner');
define('AD_POSITION_HOME_SIDEBAR', 'home_sidebar');
define('AD_POSITION_ORDER_BANNER', 'order_banner');
define('AD_POSITION_ORDER_SIDEBAR', 'order_sidebar');
define('AD_POSITION_DETAIL_BANNER', 'detail_banner');
define('AD_POSITION_USER_BANNER', 'user_banner');
define('AD_POSITION_FLOAT_AD', 'float_ad');
define('AD_POSITION_DIALOG_AD', 'dialog_ad');
define('AD_POSITION_APP_BANNER', 'app_banner');

header('Content-Type: application/json');

$pdo = adGetPdo();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 不需要登录的公开接口列表
$publicActions = ['get_ad', 'get_ads_by_position', 'record_view', 'record_click'];

// 检查是否需要登录
if (!in_array($action, $publicActions) && !isset($_SESSION['ad_admin_id'])) {
    echo json_encode(['code' => 401, 'msg' => '请先登录']);
    exit;
}

switch ($action) {
    // ==================== 广告位置管理 ====================
    case 'get_positions':
        echo json_encode(getPositions($pdo));
        break;
        
    case 'get_position':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(getPosition($pdo, $id));
        break;
        
    case 'save_position':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(savePosition($pdo, $data));
        break;
        
    case 'delete_position':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(deletePosition($pdo, $id));
        break;
        
    // ==================== 广告管理 ====================
    case 'get_ads':
        echo json_encode(getAds($pdo));
        break;
        
    case 'get_ad_by_id':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(getAd($pdo, $id));
        break;
        
    case 'save_ad':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(saveAd($pdo, $data));
        break;
        
    case 'delete_ad':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(deleteAd($pdo, $id));
        break;
        
    case 'upload_ad_image':
        echo json_encode(uploadAdImage($pdo));
        break;
        
    // ==================== 广告前端调用 ====================
    case 'record_view':
        $adId = intval($_GET['id'] ?? 0);
        $position = $_GET['position'] ?? '';
        if ($adId > 0) {
            AdApi::recordView($adId, $position);
        }
        echo json_encode(['code' => 200, 'msg' => 'success']);
        break;
        
    case 'record_click':
        $adId = intval($_GET['id'] ?? 0);
        $position = $_GET['position'] ?? '';
        if ($adId > 0) {
            AdApi::recordClick($adId, $position);
        }
        echo json_encode(['code' => 200, 'msg' => 'success']);
        break;

    case 'get_ad':
        $position = $_GET['position'] ?? '';
        if (!empty($position)) {
            $ad = AdApi::getRandomAd($position);
            if ($ad) {
                echo json_encode(['code' => 200, 'data' => $ad]);
            } else {
                echo json_encode(['code' => 404, 'msg' => '没有找到广告']);
            }
        } else {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
        }
        break;

    case 'get_ads_by_position':
        $position = $_GET['position'] ?? '';
        $limit = intval($_GET['limit'] ?? 1);
        if (!empty($position)) {
            $ads = AdApi::getAdsByPosition($position, $limit);
            echo json_encode(['code' => 200, 'data' => $ads]);
        } else {
            echo json_encode(['code' => 400, 'msg' => '参数错误']);
        }
        break;
        
    // ==================== 广告关联管理 ====================
    case 'get_relations':
        echo json_encode(getRelations($pdo));
        break;
        
    case 'save_relation':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode(saveRelation($pdo, $data));
        break;
        
    case 'delete_relation':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(deleteRelation($pdo, $id));
        break;
        
    case 'toggle_relation_status':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(toggleRelationStatus($pdo, $id));
        break;
    case 'toggle_ad_status':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $status = intval($data['status'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['code' => 400, 'msg' => '无效的广告ID']);
            break;
        }
        $pdo = adGetPdo();
        $sql = "UPDATE ads SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':status' => $status, ':id' => $id]);
        if ($result) {
            echo json_encode(['code' => 200, 'msg' => '状态更新成功']);
        } else {
            echo json_encode(['code' => 500, 'msg' => '状态更新失败']);
        }
    break;

    case 'logout':
        session_destroy();
        echo json_encode(['code' => 200, 'msg' => '已退出登录']);
        break;

    case 'change_password':
        $data = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['code' => 400, 'msg' => '密码不能为空']);
            break;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['code' => 400, 'msg' => '新密码至少6位']);
            break;
        }

        $adminId = $_SESSION['ad_admin_id'];
        $stmt = $pdo->prepare("SELECT * FROM ad_admin_users WHERE id = :id");
        $stmt->execute([':id' => $adminId]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['code' => 404, 'msg' => '用户不存在']);
            break;
        }

        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['code' => 400, 'msg' => '当前密码错误']);
            break;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE ad_admin_users SET password = :password WHERE id = :id");
        $result = $updateStmt->execute([
            ':password' => $hashedPassword,
            ':id' => $adminId
        ]);

        if ($result) {
            echo json_encode(['code' => 200, 'msg' => '密码修改成功']);
        } else {
            echo json_encode(['code' => 500, 'msg' => '密码修改失败']);
        }
        break;

    case 'get_site_info':
        echo json_encode([
            'code' => 200,
            'data' => [
                'site_name' => defined('AD_SITE_NAME') ? AD_SITE_NAME : '广告系统',
                'icp' => defined('AD_ICP') ? AD_ICP : '',
                'copyright' => defined('AD_COPYRIGHT') ? AD_COPYRIGHT : '',
                'version' => defined('AD_VERSION') ? AD_VERSION : 'v1.0.0'
            ]
        ]);
        break;

    default:
        echo json_encode(['code' => 404, 'msg' => '未找到该操作']);
}

// ==================== 广告位置函数 ====================

function getPositions($pdo) {
    $sql = "SELECT * FROM ad_positions ORDER BY priority DESC, id ASC";
    $stmt = $pdo->query($sql);
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return ['code' => 200, 'msg' => 'success', 'data' => $positions];
}

function getPosition($pdo, $id) {
    $sql = "SELECT * FROM ad_positions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $position = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$position) {
        return ['code' => 404, 'msg' => '位置不存在'];
    }
    return ['code' => 200, 'msg' => 'success', 'data' => $position];
}

function savePosition($pdo, $data) {
    $id = intval($data['id'] ?? 0);
    $code = trim($data['code'] ?? '');
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $width = intval($data['width'] ?? 0);
    $height = intval($data['height'] ?? 0);
    $image_url = trim($data['image_url'] ?? '');
    $status = intval($data['status'] ?? 1);
    $priority = intval($data['priority'] ?? 0);
    
    if (empty($code) || empty($name)) {
        return ['code' => 400, 'msg' => '标识符和名称不能为空'];
    }
    
    // 检查code唯一性
    if ($id > 0) {
        $checkSql = "SELECT id FROM ad_positions WHERE code = :code AND id != :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':code' => $code, ':id' => $id]);
    } else {
        $checkSql = "SELECT id FROM ad_positions WHERE code = :code";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':code' => $code]);
    }
    if ($checkStmt->fetch()) {
        return ['code' => 400, 'msg' => '标识符已存在'];
    }
    
    if ($id > 0) {
        $sql = "UPDATE ad_positions SET code = :code, name = :name, description = :description, width = :width, height = :height, image_url = :image_url, status = :status, priority = :priority WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':code' => $code,
            ':name' => $name,
            ':description' => $description,
            ':width' => $width,
            ':height' => $height,
            ':image_url' => $image_url,
            ':status' => $status,
            ':priority' => $priority
        ]);
    } else {
        $sql = "INSERT INTO ad_positions (code, name, description, width, height, image_url, status, priority) VALUES (:code, :name, :description, :width, :height, :image_url, :status, :priority)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':code' => $code,
            ':name' => $name,
            ':description' => $description,
            ':width' => $width,
            ':height' => $height,
            ':image_url' => $image_url,
            ':status' => $status,
            ':priority' => $priority
        ]);
    }
    
    if ($result) {
        return ['code' => 200, 'msg' => '保存成功'];
    }
    return ['code' => 500, 'msg' => '保存失败'];
}

function deletePosition($pdo, $id) {
    // 先删除关联
    $pdo->prepare("DELETE FROM ad_position_relations WHERE position_id = :id")->execute([':id' => $id]);
    
    $sql = "DELETE FROM ad_positions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        return ['code' => 200, 'msg' => '删除成功'];
    }
    return ['code' => 500, 'msg' => '删除失败'];
}

// ==================== 广告函数 ====================

function getAds($pdo) {
    $sql = "SELECT * FROM ads ORDER BY sort DESC, id DESC";
    $stmt = $pdo->query($sql);
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return ['code' => 200, 'msg' => 'success', 'data' => $ads];
}

function getAd($pdo, $id) {
    $sql = "SELECT * FROM ads WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ad) {
        return ['code' => 404, 'msg' => '广告不存在'];
    }
    return ['code' => 200, 'msg' => 'success', 'data' => $ad];
}

function saveAd($pdo, $data) {
    $id = intval($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $image_url = trim($data['image_url'] ?? '');
    $link_url = trim($data['link_url'] ?? '');
    $content = trim($data['content'] ?? '');
    $type = intval($data['type'] ?? 1);
    $status = intval($data['status'] ?? 0);
    $start_time = trim($data['start_time'] ?? '');
    $end_time = trim($data['end_time'] ?? '');
    $sort = intval($data['sort'] ?? 0);
    
    if (empty($title)) {
        return ['code' => 400, 'msg' => '标题不能为空'];
    }
    
    if ($id > 0) {
        $sql = "UPDATE ads SET title = :title, image_url = :image_url, link_url = :link_url, 
                content = :content, type = :type, status = :status, 
                start_time = :start_time, end_time = :end_time, sort = :sort 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':image_url' => $image_url,
            ':link_url' => $link_url,
            ':content' => $content,
            ':type' => $type,
            ':status' => $status,
            ':start_time' => $start_time ?: null,
            ':end_time' => $end_time ?: null,
            ':sort' => $sort
        ]);
    } else {
        $sql = "INSERT INTO ads (title, image_url, link_url, content, type, status, start_time, end_time, sort) 
                VALUES (:title, :image_url, :link_url, :content, :type, :status, :start_time, :end_time, :sort)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':title' => $title,
            ':image_url' => $image_url,
            ':link_url' => $link_url,
            ':content' => $content,
            ':type' => $type,
            ':status' => $status,
            ':start_time' => $start_time ?: null,
            ':end_time' => $end_time ?: null,
            ':sort' => $sort
        ]);
    }
    
    if ($result) {
        return ['code' => 200, 'msg' => '保存成功'];
    }
    return ['code' => 500, 'msg' => '保存失败'];
}

function deleteAd($pdo, $id) {
    // 先删除关联
    $pdo->prepare("DELETE FROM ad_position_relations WHERE ad_id = :id")->execute([':id' => $id]);
    
    $sql = "DELETE FROM ads WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        return ['code' => 200, 'msg' => '删除成功'];
    }
    return ['code' => 500, 'msg' => '删除失败'];
}

function uploadAdImage($pdo) {
    if (!isset($_FILES['image'])) {
        return ['code' => 400, 'msg' => '没有上传文件'];
    }
    
    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['code' => 400, 'msg' => '上传失败'];
    }
    
    // 检查文件类型
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return ['code' => 400, 'msg' => '只支持 JPG、PNG、GIF、WebP 格式'];
    }
    
    // 创建上传目录
    $uploadDir = __DIR__ . '/ads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'ad_' . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = '/ad/ads/' . $filename;
        return ['code' => 200, 'msg' => '上传成功', 'data' => ['url' => $url]];
    }
    
    return ['code' => 500, 'msg' => '保存文件失败'];
}

// ==================== 广告关联函数 ====================

function getRelations($pdo) {
    $sql = "SELECT r.*, p.name as position_name, p.code as position_code, a.title as ad_title, a.type as ad_type
            FROM ad_position_relations r
            LEFT JOIN ad_positions p ON r.position_id = p.id
            LEFT JOIN ads a ON r.ad_id = a.id
            ORDER BY r.id DESC";
    $stmt = $pdo->query($sql);
    $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return ['code' => 200, 'msg' => 'success', 'data' => $relations];
}

function saveRelation($pdo, $data) {
    $id = intval($data['id'] ?? 0);
    $position_id = intval($data['position_id'] ?? 0);
    $ad_id = intval($data['ad_id'] ?? 0);
    $weight = intval($data['weight'] ?? 1);
    $status = intval($data['status'] ?? 1);
    
    if ($position_id <= 0 || $ad_id <= 0) {
        return ['code' => 400, 'msg' => '请选择位置和广告'];
    }
    
    if ($id > 0) {
        $sql = "UPDATE ad_position_relations SET position_id = :position_id, ad_id = :ad_id, 
                weight = :weight, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':position_id' => $position_id,
            ':ad_id' => $ad_id,
            ':weight' => $weight,
            ':status' => $status
        ]);
    } else {
        $sql = "INSERT INTO ad_position_relations (position_id, ad_id, weight, status) 
                VALUES (:position_id, :ad_id, :weight, :status)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':position_id' => $position_id,
            ':ad_id' => $ad_id,
            ':weight' => $weight,
            ':status' => $status
        ]);
    }
    
    if ($result) {
        return ['code' => 200, 'msg' => '保存成功'];
    }
    return ['code' => 500, 'msg' => '保存失败'];
}

function deleteRelation($pdo, $id) {
    $sql = "DELETE FROM ad_position_relations WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        return ['code' => 200, 'msg' => '删除成功'];
    }
    return ['code' => 500, 'msg' => '删除失败'];
}

function toggleRelationStatus($pdo, $id) {
    $sql = "UPDATE ad_position_relations SET status = IF(status = 1, 0, 1) WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);
    
    if ($result) {
        return ['code' => 200, 'msg' => '切换成功'];
    }
    return ['code' => 500, 'msg' => '切换失败'];
}

/**
 * 广告系统API类
 * 提供广告获取、统计等功能
 */
class AdApi {
    private static $pdoInstance = null;

    public static function getPdo() {
        if (self::$pdoInstance === null) {
            self::$pdoInstance = adGetPdo();
        }
        return self::$pdoInstance;
    }
    
    /**
     * 获取指定位置的广告
     * @param string $positionCode 位置标识符
     * @param int $limit 返回数量，默认1
     * @return array 广告数据
     */
    public static function getAdsByPosition($positionCode, $limit = 1) {
        try {
            $pdo = self::getPdo();
        } catch (Exception $e) {
            return [];
        }
        
        $now = date('Y-m-d H:i:s');
        
        try {
            $sql = "SELECT a.*, apr.weight 
                    FROM ad_position_relations apr
                    INNER JOIN ad_positions ap ON apr.position_id = ap.id
                    INNER JOIN ads a ON apr.ad_id = a.id
                    WHERE ap.code = ? 
                      AND ap.status = 1 
                      AND apr.status = 1 
                      AND a.status = 1
                      AND (a.start_time IS NULL OR a.start_time <= ?)
                      AND (a.end_time IS NULL OR a.end_time >= ?)
                    ORDER BY apr.weight DESC, a.sort DESC, RAND()
                    LIMIT ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$positionCode, $now, $now, intval($limit)]);
            
            $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($ads as &$ad) {
                if (!empty($ad['image_url'])) {
                    $domain = defined('AD_DOMAIN') ? AD_DOMAIN : 'https://example.com';
                    $ad['image_url'] = $domain . '/' . ltrim($ad['image_url'], '/');
                }
            }
            unset($ad);
            
            return $ads;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取单个广告（随机返回一个）
     * @param string $positionCode 位置标识符
     * @return array|null 广告数据
     */
    public static function getRandomAd($positionCode) {
        $ads = self::getAdsByPosition($positionCode, 1);
        return !empty($ads) ? $ads[0] : null;
    }
    
    /**
     * 记录广告展示
     * @param int $adId 广告ID
     * @param string $positionCode 位置标识符
     * @param string $udid 用户UDID
     */
    public static function recordView($adId, $positionCode, $udid = '') {
        $pdo = self::getPdo();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO ad_statistics (ad_id, position_code, udid, ip, type) VALUES (:ad_id, :position_code, :udid, :ip, 'view')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ad_id' => intval($adId),
            ':position_code' => $positionCode,
            ':udid' => $udid,
            ':ip' => $ip
        ]);
        
        $updateSql = "UPDATE ads SET view_count = view_count + 1 WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':id' => intval($adId)]);
    }
    
    /**
     * 记录广告点击
     * @param int $adId 广告ID
     * @param string $positionCode 位置标识符
     * @param string $udid 用户UDID
     */
    public static function recordClick($adId, $positionCode, $udid = '') {
        $pdo = self::getPdo();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO ad_statistics (ad_id, position_code, udid, ip, type) VALUES (:ad_id, :position_code, :udid, :ip, 'click')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ad_id' => intval($adId),
            ':position_code' => $positionCode,
            ':udid' => $udid,
            ':ip' => $ip
        ]);
        
        $updateSql = "UPDATE ads SET click_count = click_count + 1 WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':id' => intval($adId)]);
    }
    
    /**
     * 获取位置信息
     * @param string $code 位置标识符
     * @return array|null 位置数据
     */
    public static function getPositionByCode($code) {
        try {
            $pdo = self::getPdo();
        } catch (Exception $e) {
            return null;
        }
        
        try {
            $sql = "SELECT * FROM ad_positions WHERE code = :code AND status = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':code' => $code]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
}

/**
 * 获取广告组件HTML
 * @param string $positionCode 位置标识符
 * @param array $options 配置选项
 * @return string HTML字符串
 */
function getAdWidget($positionCode, $options = []) {
    $ad = AdApi::getRandomAd($positionCode);

    if (!$ad) {
        return isset($options['empty']) ? $options['empty'] : '';
    }

    $position = AdApi::getPositionByCode($positionCode);
    $width = $options['width'] ?? ($position['width'] ?? '100%');
    $height = $options['height'] ?? ($position['height'] ?? 'auto');
    $class = htmlspecialchars($options['class'] ?? '');
    $showClose = isset($options['show_close']) ? $options['show_close'] : true;

    $closeBtn = $showClose ? '<button class="ad-close" onclick="closeAd(this, event)" style="position:absolute;top:5px;right:5px;width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,0.5);color:#fff;border:none;cursor:pointer;line-height:24px;text-align:center;font-size:14px;">×</button>' : '';

    if ($ad['type'] == 1) {
        $imageUrl = htmlspecialchars($ad['image_url']);
        $linkUrl = htmlspecialchars($ad['link_url'] ?: 'javascript:void(0)');
        $title = htmlspecialchars($ad['title']);
        $adId = intval($ad['id']);
        $posCode = htmlspecialchars($positionCode);

        $html = '<div class="ad-widget ' . $class . '" data-ad-id="' . $adId . '" data-position="' . $posCode . '" style="width:' . $width . ';height:' . $height . ';position:relative;">';
        $html .= '<a href="' . $linkUrl . '" target="_blank" class="ad-link" onclick="adClick(' . $adId . ', \'' . $posCode . '\')">';
        $html .= '<img src="' . $imageUrl . '" alt="' . $title . '" class="ad-image" style="width:100%;height:100%;object-fit:cover;">';
        $html .= '</a>';
        $html .= $closeBtn;
        $html .= '</div>';

    } elseif ($ad['type'] == 2) {
        $content = htmlspecialchars($ad['content']);
        $linkUrl = htmlspecialchars($ad['link_url'] ?: 'javascript:void(0)');
        $adId = intval($ad['id']);
        $posCode = htmlspecialchars($positionCode);

        $html = '<div class="ad-widget ' . $class . '" data-ad-id="' . $adId . '" data-position="' . $posCode . '" style="width:' . $width . ';padding:10px;background:#f5f5f5;border-radius:4px;">';
        $html .= '<a href="' . $linkUrl . '" target="_blank" class="ad-link" onclick="adClick(' . $adId . ', \'' . $posCode . '\')">' . $content . '</a>';
        $html .= $closeBtn;
        $html .= '</div>';

    } elseif ($ad['type'] == 3) {
        $adContent = $ad['content'];
        $adId = intval($ad['id']);
        $posCode = htmlspecialchars($positionCode);

        $html = '<div class="ad-widget ' . $class . '" data-ad-id="' . $adId . '" data-position="' . $posCode . '" style="width:' . $width . ';">';
        $html .= $adContent;
        $html .= $closeBtn;
        $html .= '</div>';

    } else {
        return '';
    }

    AdApi::recordView($ad['id'], $positionCode);

    static $scriptAdded = false;
    if (!$scriptAdded) {
        $scriptAdded = true;
        $script = <<<EOT
<script>
function closeAd(btn, e) {
    if(e) e.preventDefault();
    var widget = btn.closest('.ad-widget');
    if(widget) {
        widget.style.display = 'none';
        localStorage.setItem('ad_closed_' + widget.dataset.position, '1');
    }
}
function adClick(adId, position) {
    fetch('/ad/api.php?action=record_click&id=' + adId + '&position=' + position).catch(function(){});
}
</script>
EOT;
        $html .= $script;
    }

    return $html;
}
?>
