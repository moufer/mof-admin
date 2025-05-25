import express from "express";
import { createServer } from "http";
import { Server } from "socket.io";
import { fileURLToPath } from "url";
import { dirname, join } from "path";
import { watch } from "chokidar";

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const app = express();
const server = createServer(app);
const io = new Server(server);

const PORT = process.env.PORT || 5050;
const ROOT_DIR = __dirname;

// 提供静态文件服务
app.use(express.static(ROOT_DIR));

// 处理所有路由，返回index.html
app.get("*", (req, res) => {
  res.sendFile(join(ROOT_DIR, "index.html"));
});

// 监听文件变化
const watcher = watch(ROOT_DIR, {
  ignored: /(^|[\/\\])\..|(node_modules)|(dist)|(build)/,
  persistent: true,
});

// 当文件变化时通知客户端刷新
watcher.on("change", (path) => {
  console.log(`文件已更改: ${path}`);
  io.emit("fileChanged");
});

// 启动服务器
server.listen(PORT, () => {
  console.log(`服务器运行在 http://localhost:${PORT}`);
  console.log(`监听目录: ${ROOT_DIR}`);
});
