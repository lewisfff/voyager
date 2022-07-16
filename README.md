this website allows anonymous users to upload bsp files and prepare them for use in the game.

server setup:

symlink the bsp files to the server's maps folder.

ln -s ./storage/cstrike/maps server/cstrike/maps

configure the game server to use the fastdl server.

cstrike/cfg/server.cfg

sv_downloadurl "http://ip.address/fastdl/"
sv_allowdownload 1
