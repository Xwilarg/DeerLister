var audioIndex = 0;

const songs = Array.from(document.getElementsByClassName("audio-preview"));
const player = document.getElementById("audio-player");
player.volume = 0.25;
if (songs.length > 0)
{
    player.src = songs[0];
}
for (const a of songs) {
    const href = a.href;
    a.addEventListener("click", e => {
        e.preventDefault();
        player.src = href;
        player.play();
    });
}
player.addEventListener("ended", () => {
    if (audioIndex < songs.length - 1) {
        audioIndex++;
        player.src = songs[audioIndex].href;
        player.play();
    }
});