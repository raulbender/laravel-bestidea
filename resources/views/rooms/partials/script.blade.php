<script>
    function roomBoard(roomUuid, roomId = null) {
        return {
            uuid: roomUuid,
            roomId: roomId,
            roomData: {},
            roomOwner: '',
            myPersona: null,
            ideas: [],
            loading: false,
            showForm: false,
            submitting: false,
            newIdeaContent: '',
            sortBy: 'recent',
            filterMine: false,
            roomUrl: window.location.href,
            
            activeIdea: null,
            activeTab: 'comments',
            comments: [],
            ratings: [],
            newComment: '',

            toast: {
                show: false,
                message: '',
                icon: '✅'
            },

            async init() {
                await this.fetchRoomDetails();
                await this.fetchIdeas();
            },

            showToast(msg, icon = '✅') {
                this.toast.message = msg;
                this.toast.icon = icon;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },

            openForm() {
                this.showForm = true;
                this.$nextTick(() => {
                    this.$refs.ideaTextarea.focus();
                });
            },

            copyLink() {
                navigator.clipboard.writeText(this.roomUrl);
                this.showToast('Link copiado!', '📋');
            },

            setSort(type) {
                this.filterMine = false;
                this.sortBy = type;
                this.fetchIdeas();
            },

            toggleMine() {
                this.filterMine = !this.filterMine;
                this.fetchIdeas();
            },

            openIdeaDetails(idea) {
                this.activeIdea = idea;
                this.activeTab = 'comments';
                this.fetchComments(idea.id);
                this.fetchRatings(idea.id);
            },

            closeIdeaDetails() {
                this.activeIdea = null;
                this.comments = [];
                this.ratings = [];
                this.newComment = '';
            },

            async fetchRoomDetails() {
                try {
                    const response = await fetch(`/api/rooms/${this.uuid}`);
                    const json = await response.json();
                    const payload = json.data || json;
                    this.roomData = payload;
                    this.roomOwner = payload.owner_name || 'Anônimo';
                    this.myPersona = payload.my_persona || null;
                    this.roomId = this.roomData.id || this.roomId;
                } catch (error) {
                    console.error(error);
                }
            },

            async fetchIdeas() {
                this.loading = true;
                try {
                    let url = `/api/ideas?sort=${this.sortBy}&room_uuid=${this.uuid}`;
                    if (this.filterMine) url += `&filter=mine`;
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const json = await response.json();
                    this.ideas = json.data || json;
                } catch (error) {
                    console.error(error);
                    this.ideas = [];
                } finally {
                    this.loading = false;
                }
            },

            async submitIdea() {
                if (this.newIdeaContent.trim().length < 5 || this.submitting) return;
                this.submitting = true;
                try {
                    const response = await fetch(`/api/rooms/${this.uuid}/ideas`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.newIdeaContent,
                            room_uuid: this.uuid
                        })
                    });

                    if (response.ok) {
                        this.newIdeaContent = '';
                        this.showForm = false;
                        this.showToast('Ideia publicada!', '🎉');
                        this.fetchIdeas();
                    }
                } catch (error) {
                    this.showToast('Erro ao enviar', '❌');
                } finally {
                    this.submitting = false;
                }
            },

            async fetchComments(ideaId) {
                try {
                    const response = await fetch(`/api/ideas/${ideaId}/comments?room_uuid=${this.uuid}`);
                    if (response.ok) {
                        const json = await response.json();
                        this.comments = json.data || json;
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            async fetchRatings(ideaId) {
                try {
                    const response = await fetch(`/api/ideas/${ideaId}/ratings?room_uuid=${this.uuid}`);
                    if (response.ok) {
                        const json = await response.json();
                        this.ratings = json.data || json;
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            async submitComment() {
                if (!this.newComment.trim() || !this.activeIdea) return;
                try {
                    const response = await fetch(`/api/ideas/${this.activeIdea.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.newComment,
                            room_uuid: this.uuid
                        })
                    });

                    if (response.ok) {
                        this.newComment = '';
                        this.fetchComments(this.activeIdea.id);
                        this.activeIdea.comments_count = (this.activeIdea.comments_count || 0) + 1;
                        this.showToast('Comentário enviado!', '💬');
                    }
                } catch (error) {
                    this.showToast('Erro ao comentar', '❌');
                }
            },

            async rateIdea(ideaId, score) {
                try {
                    await fetch(`/api/ideas/${ideaId}/ratings`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            score: score,
                            room_uuid: this.uuid
                        })
                    });
                    this.showToast('Avaliação salva!', '⭐');
                    if (this.activeIdea) {
                        this.fetchRatings(ideaId);
                    }
                    this.fetchIdeas();
                } catch (error) {
                    console.error(error);
                }
            }
        }
    }
</script>