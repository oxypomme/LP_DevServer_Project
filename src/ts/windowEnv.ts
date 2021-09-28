type WindowEnv = {
  PHP_MODE: string | "production";
};

export default (window as any).env as WindowEnv;
