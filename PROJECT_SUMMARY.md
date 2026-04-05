# TDHGame 工程总结文档

> 创建时间: 2026年4月5日
> 最后更新: 2026年4月5日

---

## 1. 项目概述

**TDHGame** 是一个 PHP 后端游戏服务器项目，为移动端（Android/iOS Capacitor）游戏提供 API 服务。项目采用传统的 PHP 单文件入口架构，结合 MySQL、MongoDB 和 Redis 实现数据持久化和缓存。

### 技术栈

| 组件 | 技术 |
|------|------|
| 后端语言 | PHP 8.x |
| Web服务器 | Apache/Nginx |
| 主数据库 | MySQL (utf8mb4) |
| 文档数据库 | MongoDB |
| 缓存/会话 | Redis |
| 依赖管理 | Composer |

### 依赖库

- `predis/predis`: PHP Redis 客户端
- `mongodb/mongodb`: PHP MongoDB 客户端

---

## 2. 项目结构

```
TDHGame/
├── api.php                 # 主API入口，处理所有游戏请求
├── index.php               # 测试入口 (phpinfo)
├── main.php                # 测试入口 (phpinfo)
├── webhook.php             # GitHub Webhook 自动部署脚本
├── composer.json           # Composer配置
├── migration_script.sql    # MySQL数据库迁移脚本
│
├── common/
│   └── common.php          # 公共函数库 (CSV读取)
│
├── config/
│   ├── constdef.php        # 常量定义 (VERSION)
│   └── database.php        # 数据库配置 (MySQL/MongoDB)
│
├── core/
│   └── dbconn.php          # 数据库连接类 (MySQL PDO / MongoDB)
│
├── data/
│   ├── const.ini           # 游戏配置 (版本号)
│   ├── RoleConfig.php      # 角色等级配置 (1-15级属性)
│   ├── StageConfig.php     # 关卡配置
│   ├── MonsterAlloc.php    # 怪物分配配置
│   ├── MonsterAttributes.php # 怪物属性配置
│   └── DropAward.php       # 掉落奖励配置
│
├── models/
│   ├── user.php            # 用户模型
│   ├── role.php            # 角色模型 (等级、属性)
│   ├── stage.php           # 关卡模型 (关卡信息、掉落)
│   └── gamedata.php        # 游戏数据控制器 (战斗、进度)
│
└── services/
    ├── userservice.php     # 用户服务 (登录/注册/验证)
    └── tokenmanager.php    # Token管理 (Redis)
```

---

## 3. 数据库设计

### MySQL 表结构

#### account (用户账户)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT | 自增主键 |
| account | VARCHAR(50) | 账户名 (唯一) |
| userid | INT | 用户ID |
| money | INT | 货币 |

#### auth_device (设备认证)
| 字段 | 类型 | 说明 |
|------|------|------|
| token | VARCHAR | 认证Token |
| account | VARCHAR | 账户名 |

#### game_data (游戏进度)
| 字段 | 类型 | 说明 |
|------|------|------|
| userid | INT | 用户ID |
| unlockprogress | VARCHAR | 解关进度 (如 "1-2") |
| currprogress | VARCHAR | 当前进度 |
| coin | INT | 金币 |
| diamond | INT | 钻石 |

> 注: 角色数据已迁移到 MongoDB (roles集合)

### MongoDB 集合

#### roles (角色数据)
```json
{
  "user_id": 900001,
  "level": 1,
  "exp": 0,
  "skills_equip": "",
  "skills": []
}
```

### Redis 存储

| Key格式 | 说明 | 过期时间 |
|---------|------|----------|
| `player:token:{token}` | 用户Token映射 | 24小时 |
| `auth_salt:{saltId}` | 登录盐值 | 5分钟 |
| `limit:salt:{ip}` | IP限流计数器 | 1分钟 |

---

## 4. API 接口列表

### 基础接口

| reqtype | 说明 | 参数 |
|---------|------|------|
| `salt` | 获取登录盐值 | - |
| `register` | 用户注册 | token, sign, saltId, timestamp |
| `login` | 用户登录 | account |
| `token_login` | Token登录 | token |
| `gameinfo` | 获取游戏版本 | - |
| `stageinfo` | 获取关卡信息 | stageid, phaseid |
| `uniMessage` | 游戏统一消息 | userid, token, reason, sPara, nPara |

### uniMessage 消息类型 (reason)

| 值 | 说明 | 参数 |
|----|------|------|
| 1 | battleStart (战斗开始) | sPara: progress, exp, coin, diamond; nPara: level |
| 2 | battleClear (战斗胜利) | 同上 |
| 3 | battleFail (战斗失败) | 同上 |
| 4 | getRoleData (获取角色) | nPara: level |
| 5 | saveSkillsEquip (保存技能) | sPara: skillId列表 |
| 6 | skillUpgrade (技能升级) | sPara: skillId |

---

## 5. 核心模块说明

### UserService (用户服务)
- 用户注册/登录
- Token生成与验证
- 游戏消息转发

### GameDataController (游戏数据)
- 游戏进度管理 (unlockProgress/currProgress)
- 金币/钻石管理
- 角色数据管理 (MongoDB)
- 技能系统

### TokenManager (Token管理)
- 基于Redis的Token存储
- 登录盐值生成 (防暴力破解)
- IP限流

### Stage (关卡系统)
- 关卡配置加载
- 怪物分配
- 掉落奖励计算

---

## 6. 认证流程

```
1. 客户端请求 salt -> 获取 saltId + salt
2. 客户端: sign = md5(token ":" salt ":" timestamp)
3. 客户端请求 register/register 提交 token, sign, saltId, timestamp
4. 服务端验证:
   - 时间戳在5分钟内
   - salt未过期
   - sign匹配
5. 注册成功返回 account
6. 后续请求使用 token_login 或 login
```

---

## 7. 关键常量

### 角色属性
```php
ROLE_HP = 100       // 初始血量
ROLE_LV = 1         // 初始等级
ROLE_EXP = 0        // 初始经验
ROLE_SP = 0         // 技能点
ROLE_ATK = 20       // 攻击力
ROLE_DEF = 5        // 防御力
ROLE_CRI = 5        // 暴击率
ROLE_CRD = 120      // 暴击伤害
ROLE_ATK_RATE = 2   // 攻击频率(秒)
```

---

## 8. 开发注意事项

1. **CORS配置**: `api.php` 中配置了 Capacitor 移动端允许的Origins
2. **数据库连接**: 使用单例模式 (DbConn/MongoConn)
3. **Token存储**: 24小时过期，客户端需处理过期重新登录
4. **安全验签**: 注册时使用 MD5 验签，需与客户端算法一致
5. **WebHook**: `webhook.php` 用于 GitHub 自动部署 (Linux路径)

---

## 9. 后续可能的改进方向

- 分离 API 到 Controller 层
- 引入框架 (Laravel/Symfony)
- 添加日志系统
- 添加请求频率限制
- 完善错误处理
- 添加单元测试