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

            userScore: null,
            selectedScore: null,

            // Estado da Expansão do Rating Inline (Feed)
            expandedRatingIdeaId: null,
            currentRatingId: null,
            ratingComment: '',
            isRatingSubmitting: false,

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

            // --- MÉTODOS DO RATING INLINE ---
            async toggleRating(idea) {
                if (this.expandedRatingIdeaId === idea.id) {
                    this.closeRatingInline();
                } else {
                    this.expandedRatingIdeaId = idea.id;
                    this.currentRatingId = null;
                    this.ratingComment = '';
                    this.userScore = idea.my_rating || null;
                    this.selectedScore = this.userScore;

                    // Se a nota não veio no payload inicial do feed, busca sob demanda:
                    if (this.userScore === null) {
                        await this.fetchMyRating(idea.id);
                    }
                }
            },

            closeRatingInline() {
                this.expandedRatingIdeaId = null;
                this.currentRatingId = null;
                this.ratingComment = '';
                this.isRatingSubmitting = false;
            },

            async fetchMyRating(ideaId) {
                try {
                    const response = await fetch(`/api/ideas/${ideaId}/my-rating?room_uuid=${this.uuid}`);
                    if (response.ok) {
                        const data = await response.json();
                        this.userScore = data.score || null;
                        this.selectedScore = this.userScore;
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            async rateIdeaInline(idea, score) {
                this.isRatingSubmitting = true;
                this.selectedScore = score;

                try {
                    const response = await fetch(`/api/ideas/${idea.id}/ratings`, {
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

                    if (response.ok) {
                        const data = await response.json();
                        // Mantém o id da avaliação retornado para liberar o campo de comentário
                        this.currentRatingId = data.id || data.rating?.id || true;
                        this.userScore = score;
                        idea.my_rating = score;

                        this.showToast('Nota registrada!', '⭐');
                        this.fetchIdeas(); // Recarrega médias do feed sem fechar o accordion
                    }
                } catch (error) {
                    this.showToast('Erro ao avaliar', '❌');
                } finally {
                    this.isRatingSubmitting = false;
                }
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

            async submitRatingComment(idea) {
                if (!this.ratingComment.trim()) return;

                try {
                    const response = await fetch(`/api/ideas/${idea.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.ratingComment,
                            attach_rating: true, // Avisa o backend para associar ao rating
                            room_uuid: this.uuid
                        })
                    });

                    if (response.ok) {
                        this.showToast('Comentário enviado!', '💬');
                        this.closeRatingInline();
                        this.fetchIdeas();
                    }
                } catch (error) {
                    this.showToast('Erro ao enviar', '❌');
                }
            },

            // --- MÉTODOS DO MODAL DE DISCUSSÃO ---
            openIdeaComments(idea) {
                this.closeRatingInline();
                this.activeIdea = idea;
                this.fetchComments(idea.id);
                this.fetchRatings(idea.id);
            },

            closeIdeaComments() {
                this.activeIdea = null;
                this.comments = [];
                this.ratings = [];
                this.newComment = '';
            },

            // async rateIdea(ideaId, score) {
            //     try {
            //         await fetch(`/api/ideas/${ideaId}/ratings`, {
            //             method: 'POST',
            //             headers: {
            //                 'Content-Type': 'application/json',
            //                 'Accept': 'application/json',
            //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            //             },
            //             body: JSON.stringify({
            //                 score: score,
            //                 room_uuid: this.uuid
            //             })
            //         });
            //         this.showToast('Avaliação salva!', '⭐');
            //         if (this.activeIdea) {
            //             this.fetchRatings(ideaId);
            //         }
            //         this.fetchIdeas();
            //     } catch (error) {
            //         console.error(error);
            //     }
            // }
        }
    }
</script>