import { cp, mkdir, rm } from "node:fs/promises";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import { spawn } from "node:child_process";

const root = join(dirname(fileURLToPath(import.meta.url)), "..");
const assetRoot = join(root, "asset", "vendor");

const files = [
  ["node_modules/jquery/dist/jquery.min.js", "jquery/jquery.min.js"],
  ["node_modules/datatables.net/js/dataTables.min.js", "datatables/dataTables.min.js"],
  ["node_modules/datatables.net-dt/css/dataTables.dataTables.min.css", "datatables/dataTables.min.css"],
  ["node_modules/chart.js/dist/chart.umd.min.js", "chart.js/chart.umd.min.js"],
  ["node_modules/@tensorflow/tfjs/dist/tf.min.js", "tensorflow/tf.min.js"],
  ["node_modules/@tensorflow-models/toxicity/dist/toxicity.min.js", "tensorflow/toxicity.min.js"],
  ["node_modules/@fortawesome/fontawesome-free/css/all.min.css", "fontawesome/css/all.min.css"],
  ["node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2", "fontawesome/webfonts/fa-solid-900.woff2"],
  ["node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2", "fontawesome/webfonts/fa-regular-400.woff2"],
  ["node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.woff2", "fontawesome/webfonts/fa-brands-400.woff2"]
];

await rm(assetRoot, { recursive: true, force: true });

for (const [source, destination] of files) {
  const target = join(assetRoot, destination);
  await mkdir(dirname(target), { recursive: true });
  await cp(join(root, source), target);
}

await new Promise((resolve, reject) => {
  const child = spawn(process.execPath, [
    join(root, "node_modules", "@tailwindcss", "cli", "dist", "index.mjs"),
    "-i",
    "./src/css/app.css",
    "-o",
    "./asset/css/app.css",
    "--minify"
  ], {
    cwd: root,
    stdio: "inherit"
  });
  child.on("error", reject);
  child.on("exit", (code) => code === 0 ? resolve() : reject(new Error(`Tailwind build exited with code ${code}`)));
});
