{
  description = "PHP App demo";

  inputs.nixpkgs.url = "nixpkgs/nixpkgs-unstable";
  inputs.flake-utils.url = "github:numtide/flake-utils";

  outputs = { self, nixpkgs, flake-utils }: flake-utils.lib.eachDefaultSystem
    (system:
      let
        name = "PHPApp";
        port = "8080"; # Must be a string
        public = "examples";

        revision = "${self.lastModifiedDate}-${self.shortRev or "dirty"}";

        pkgs = import nixpkgs {
          inherit system;
        };

        php = pkgs.php;
        src = self;

        phpProject = pkgs.callPackage ./composer-project.nix {
          inherit php;
        } src;

        wrapper = pkgs.writeScriptBin name ''
          #!${pkgs.bash}/bin/bash
          export X_LISTEN=0.0.0.0:${port}
          ${pkgs.php}/bin/php ${phpProject}/libexec/source/${public}/index.php
        '';

        phpApp = pkgs.stdenv.mkDerivation {
          inherit name src;

          buildInputs = [
            phpProject
            wrapper
          ];

          installPhase = ''
            mkdir -p $out/bin
            cp -r ${wrapper}/bin/* $out/bin/
          '';
        };
      in
      {
        # Nix run
        defaultApp = phpApp;

        # Nix build
        packages = {
          oci = pkgs.dockerTools.buildLayeredImage {
            name = "clue/framework-x";
            tag = revision;
            contents = [ phpApp ];
            config = {
              Cmd = [ "${phpApp}/bin/PHPApp" ];
              ExposedPorts = {
                "${port}/tcp" = { };
              };
            };
          };
        };
      }
    );
}
