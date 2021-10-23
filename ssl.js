const pem = require("pem");
const { existsSync, mkdirSync, rmSync, writeFileSync } = require("fs");
const { join } = require("path");

const BASE_PATH = join(__dirname, "ssl/");

if (existsSync(BASE_PATH)) {
  rmSync(BASE_PATH, { recursive: true, force: true });
}
mkdirSync(BASE_PATH);

pem.createCertificate(
  { days: 365, selfSigned: true },
  function (err, { certificate }) {
    if (err) {
      throw err;
    }
    pem.createPrivateKey(4096, (err, { key }) => {
      if (err) {
        throw err;
      }
      writeFileSync(join(BASE_PATH, "crisis.crt"), certificate);
      writeFileSync(join(BASE_PATH, "crisis.key"), key);
    });
  }
);
